<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cart) {}

    public function index(): View
    {
        $cart = $this->cart->resolve(false);
        $cart?->load(['items.product.images', 'items.product.brand', 'items.product.category']);

        return view('cart.index', [
            'cart' => $cart,
            'items' => $cart?->items ?? collect(),
            'subtotal' => $cart?->subtotal() ?? 0,
        ]);
    }

    public function add(AddToCartRequest $request): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $product = Product::findOrFail($data['product_id']);

        try {
            $this->cart->add(
                $product,
                (int) ($data['quantity'] ?? 1),
                $data['size'] ?? null,
                $data['color'] ?? null,
            );
        } catch (RuntimeException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['cart' => $e->getMessage()]);
        }

        $count = $this->cart->count();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'محصول به سبد خرید اضافه شد.',
                'count' => $count,
            ]);
        }

        if ($request->boolean('checkout')) {
            return redirect()->route('profile.checkout')->with('status', 'محصول به سبد خرید اضافه شد.');
        }

        return back()->with('status', 'محصول به سبد خرید اضافه شد.');
    }

    public function update(UpdateCartItemRequest $request, int $item): JsonResponse|RedirectResponse
    {
        $data = $request->validated();

        $this->cart->updateQuantity($item, (int) $data['quantity']);
        $cart = $this->cart->resolve(false);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count' => $this->cart->count(),
                'subtotal' => $cart?->fresh('items')?->subtotal() ?? 0,
            ]);
        }

        return redirect()->route('cart.index');
    }

    public function remove(Request $request, int $item): JsonResponse|RedirectResponse
    {
        $this->cart->remove($item);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count' => $this->cart->count(),
            ]);
        }

        return redirect()->route('cart.index')->with('status', 'کالا از سبد خرید حذف شد.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()->route('cart.index')->with('status', 'سبد خرید خالی شد.');
    }
}
