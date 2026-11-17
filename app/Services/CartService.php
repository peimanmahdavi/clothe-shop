<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Database\DeadlockException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Session-/user-bound shopping cart.
 *
 * Concurrency safety:
 *  - Every mutation runs in a DB transaction that takes a row lock on the
 *    parent cart (and product) so two simultaneous adds cannot interleave
 *    their read-modify-write on quantity.
 *  - Product rows are re-read FOR UPDATE and stock is checked against the
 *    *live* value plus what is already in the cart, preventing overselling
 *    when the last unit is added from two tabs at once.
 *  - The (cart_id, product_id, size, color) unique index is the final guard
 *    against duplicate line items; violations trigger a short retry that
 *    re-resolves the row.
 *  - Deadlocks are retried via DB::transaction's $attempts argument.
 */
class CartService
{
    private const MAX_ATTEMPTS = 5;

    /**
     * Resolve (or lazily create) the cart for the current request.
     */
    public function resolve(bool $create = true): ?Cart
    {
        $user = Auth::user();

        if ($user) {
            $cart = Cart::where('user_id', $user->id)->first();

            if ($cart) {
                return $cart;
            }

            if (! $create) {
                return null;
            }

            return $this->retry(fn () => Cart::firstOrCreate(
                ['user_id' => $user->id],
                ['session_id' => 'user:'.$user->id],
            ));
        }

        if (! session()->isStarted()) {
            return null;
        }

        $sessionId = session()->getId();

        $cart = Cart::where('session_id', $sessionId)->first();

        if ($cart || ! $create) {
            return $cart;
        }

        return $this->retry(fn () => Cart::firstOrCreate(
            ['session_id' => $sessionId],
            ['user_id' => null],
        ));
    }

    /**
     * Add a product (or bump an existing identical line) to the cart.
     *
     * @throws RuntimeException when stock is insufficient.
     */
    public function add(Product $product, int $quantity = 1, ?string $size = null, ?string $color = null): Cart
    {
        $quantity = max(1, $quantity);
        $size = (string) ($size ?? '');
        $color = (string) ($color ?? '');

        $cart = $this->resolve();

        $this->retry(function () use ($cart, $product, $quantity, $size, $color): void {
            DB::transaction(function () use ($cart, $product, $quantity, $size, $color): void {
                // Serialize concurrent mutations of this cart.
                $lockedCart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();

                // Read the live product + stock under lock.
                $lockedProduct = Product::whereKey($product->id)->lockForUpdate()->first();

                if (! $lockedProduct || ! $lockedProduct->is_active) {
                    throw new RuntimeException('این محصول در دسترس نیست.');
                }

                $existing = CartItem::where('cart_id', $lockedCart->id)
                    ->where('product_id', $lockedProduct->id)
                    ->where('size', $size)
                    ->where('color', $color)
                    ->lockForUpdate()
                    ->first();

                $alreadyInCart = (int) CartItem::where('cart_id', $lockedCart->id)
                    ->where('product_id', $lockedProduct->id)
                    ->sum('quantity');

                $desired = ($existing?->quantity ?? 0) + $quantity;
                $otherLines = $alreadyInCart - ($existing?->quantity ?? 0);

                if ($otherLines + $desired > $lockedProduct->stock) {
                    throw new RuntimeException('موجودی کافی نیست. حداکثر '.$lockedProduct->stock.' عدد قابل سفارش است.');
                }

                $unitPrice = $lockedProduct->finalPrice();

                if ($existing) {
                    $existing->update(['quantity' => $desired, 'unit_price' => $unitPrice]);
                } else {
                    CartItem::create([
                        'cart_id' => $lockedCart->id,
                        'product_id' => $lockedProduct->id,
                        'size' => $size,
                        'color' => $color,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                    ]);
                }
            }, self::MAX_ATTEMPTS);
        });

        return $cart->fresh(['items.product']);
    }

    /**
     * Set the quantity of an existing line, clamped to available stock.
     */
    public function updateQuantity(int $itemId, int $quantity): void
    {
        $quantity = max(0, $quantity);
        $cart = $this->resolve();

        if (! $cart) {
            return;
        }

        $this->retry(function () use ($cart, $itemId, $quantity): void {
            DB::transaction(function () use ($cart, $itemId, $quantity): void {
                Cart::whereKey($cart->id)->lockForUpdate()->first();

                $item = CartItem::where('cart_id', $cart->id)->whereKey($itemId)->lockForUpdate()->first();

                if (! $item) {
                    return;
                }

                if ($quantity === 0) {
                    $item->delete();

                    return;
                }

                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                $max = $product?->stock ?? 0;

                $item->update([
                    'quantity' => min($quantity, max($max, 1)),
                    'unit_price' => $product?->finalPrice() ?? $item->unit_price,
                ]);
            }, self::MAX_ATTEMPTS);
        });
    }

    public function remove(int $itemId): void
    {
        $cart = $this->resolve(false);

        if (! $cart) {
            return;
        }

        CartItem::where('cart_id', $cart->id)->whereKey($itemId)->delete();
    }

    public function clear(): void
    {
        $cart = $this->resolve(false);

        if ($cart) {
            $cart->items()->delete();
        }
    }

    public function count(): int
    {
        $cart = $this->resolve(false);

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    /**
     * Add a product to a specific cart (used by the admin panel).
     *
     * Shares the same locking + stock guards as the storefront add.
     *
     * @throws RuntimeException when stock is insufficient.
     */
    public function addToCart(Cart $cart, Product $product, int $quantity = 1, ?string $size = null, ?string $color = null): void
    {
        $quantity = max(1, $quantity);
        $size = (string) ($size ?? '');
        $color = (string) ($color ?? '');

        $this->retry(function () use ($cart, $product, $quantity, $size, $color): void {
            DB::transaction(function () use ($cart, $product, $quantity, $size, $color): void {
                $lockedCart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
                $lockedProduct = Product::whereKey($product->id)->lockForUpdate()->first();

                if (! $lockedProduct) {
                    throw new RuntimeException('این محصول وجود ندارد.');
                }

                $existing = CartItem::where('cart_id', $lockedCart->id)
                    ->where('product_id', $lockedProduct->id)
                    ->where('size', $size)
                    ->where('color', $color)
                    ->lockForUpdate()
                    ->first();

                $alreadyInCart = (int) CartItem::where('cart_id', $lockedCart->id)
                    ->where('product_id', $lockedProduct->id)
                    ->sum('quantity');

                $desired = ($existing?->quantity ?? 0) + $quantity;
                $otherLines = $alreadyInCart - ($existing?->quantity ?? 0);

                if ($otherLines + $desired > $lockedProduct->stock) {
                    throw new RuntimeException('موجودی کافی نیست. حداکثر '.$lockedProduct->stock.' عدد قابل ثبت است.');
                }

                $unitPrice = $lockedProduct->finalPrice();

                if ($existing) {
                    $existing->update(['quantity' => $desired, 'unit_price' => $unitPrice]);
                } else {
                    CartItem::create([
                        'cart_id' => $lockedCart->id,
                        'product_id' => $lockedProduct->id,
                        'size' => $size,
                        'color' => $color,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                    ]);
                }
            }, self::MAX_ATTEMPTS);
        });
    }

    /**
     * Update a line quantity inside a specific cart (admin panel).
     */
    public function setItemQuantity(Cart $cart, int $itemId, int $quantity): void
    {
        $quantity = max(0, $quantity);

        $this->retry(function () use ($cart, $itemId, $quantity): void {
            DB::transaction(function () use ($cart, $itemId, $quantity): void {
                Cart::whereKey($cart->id)->lockForUpdate()->first();

                $item = CartItem::where('cart_id', $cart->id)->whereKey($itemId)->lockForUpdate()->first();

                if (! $item) {
                    return;
                }

                if ($quantity === 0) {
                    $item->delete();

                    return;
                }

                $product = Product::whereKey($item->product_id)->lockForUpdate()->first();
                $max = $product?->stock ?? 0;

                $item->update([
                    'quantity' => min($quantity, max($max, 1)),
                    'unit_price' => $product?->finalPrice() ?? $item->unit_price,
                ]);
            }, self::MAX_ATTEMPTS);
        });
    }

    /**
     * Remove a line from a specific cart (admin panel).
     */
    public function removeItem(Cart $cart, int $itemId): void
    {
        CartItem::where('cart_id', $cart->id)->whereKey($itemId)->delete();
    }

    /**
     * Empty a specific cart (admin panel).
     */
    public function emptyCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    /**
     * Merge a guest (session) cart into the authenticated user's cart.
     * Called right after login so items picked before auth are kept.
     */
    public function mergeGuestCartIntoUser(int $userId): void
    {
        $sessionId = session()->getId();

        $guest = Cart::where('session_id', $sessionId)->whereNull('user_id')->with('items')->first();

        if (! $guest || $guest->items->isEmpty()) {
            return;
        }

        $userCart = $this->resolve();

        $this->retry(function () use ($guest, $userCart): void {
            DB::transaction(function () use ($guest, $userCart): void {
                Cart::whereKey($userCart->id)->lockForUpdate()->first();

                foreach ($guest->items as $item) {
                    $product = Product::find($item->product_id);

                    if (! $product) {
                        continue;
                    }

                    try {
                        CartItem::create([
                            'cart_id' => $userCart->id,
                            'product_id' => $item->product_id,
                            'size' => (string) $item->size,
                            'color' => (string) $item->color,
                            'quantity' => min($item->quantity, max($product->stock, 0)),
                            'unit_price' => $product->finalPrice(),
                        ]);
                    } catch (UniqueConstraintViolationException) {
                        // Line already exists in the user cart; add quantities.
                        CartItem::where('cart_id', $userCart->id)
                            ->where('product_id', $item->product_id)
                            ->where('size', (string) $item->size)
                            ->where('color', (string) $item->color)
                            ->increment('quantity', $item->quantity);
                    }
                }

                $guest->items()->delete();
                $guest->delete();
            }, self::MAX_ATTEMPTS);
        });
    }

    /**
     * Retry an operation on transient DB conflicts (deadlocks/serialization).
     */
    private function retry(callable $callback): mixed
    {
        $attempts = 0;

        beginning:
        $attempts++;

        try {
            return $callback();
        } catch (DeadlockException|UniqueConstraintViolationException $e) {
            if ($attempts >= self::MAX_ATTEMPTS) {
                throw $e;
            }

            usleep(random_int(20000, 80000) * $attempts);

            goto beginning;
        }
    }
}
