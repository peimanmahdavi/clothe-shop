<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplyDiscountRequest;
use App\Http\Requests\PaymentCallbackRequest;
use App\Http\Requests\RemovePasswordRequest;
use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UploadReceiptRequest;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\CartService;
use App\Services\DiscountService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly DiscountService $discountService,
    ) {}

    /**
     * Show user dashboard with orders summary.
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        $user->loadCount(['orders', 'addresses']);

        $recentOrders = Order::query()
            ->where('user_id', $user->id)
            ->with('address')
            ->latest('placed_at')
            ->take(5)
            ->get();

        $stats = [
            'total_orders' => $user->orders_count,
            'pending_orders' => Order::where('user_id', $user->id)->where('status', 'pending')->count(),
            'completed_orders' => Order::where('user_id', $user->id)->where('status', 'completed')->count(),
            'total_addresses' => $user->addresses_count,
            'default_address' => $user->getDefaultAddress(),
        ];

        return view('profile.dashboard', [
            'user' => $user,
            'recentOrders' => $recentOrders,
            'stats' => $stats,
        ]);
    }

    /**
     * Show addresses list.
     */
    public function addresses(): View
    {
        $user = Auth::user();
        $addresses = $user->addresses()->orderByDesc('is_default')->orderBy('created_at')->get();

        return view('profile.addresses', [
            'user' => $user,
            'addresses' => $addresses,
        ]);
    }

    /**
     * Store a new address.
     */
    public function storeAddress(StoreAddressRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        // If this is set as default, unset other defaults
        if ($request->boolean('is_default')) {
            $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        // If this is the first address, make it default
        $isFirstAddress = $user->addresses()->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirstAddress;

        $address = $user->addresses()->create([
            'title' => $data['title'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'province' => $data['province'],
            'city' => $data['city'],
            'address_line' => $data['address_line'],
            'postal_code' => $data['postal_code'] ?? null,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت اضافه شد.',
            'address' => $address->load('user'),
        ]);
    }

    /**
     * Update an address. Ownership is enforced by UpdateAddressRequest.
     */
    public function updateAddress(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        $data = $request->validated();

        // If this is set as default, unset other defaults
        if ($request->boolean('is_default') && ! $address->is_default) {
            $address->user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address->update([
            'title' => $data['title'],
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'],
            'province' => $data['province'],
            'city' => $data['city'],
            'address_line' => $data['address_line'],
            'postal_code' => $data['postal_code'] ?? null,
            'is_default' => $request->boolean('is_default'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت بهروزرسانی شد.',
            'address' => $address->fresh(),
        ]);
    }

    /**
     * Delete an address.
     */
    public function destroyAddress(Address $address): JsonResponse
    {
        $this->authorize('delete', $address);

        $wasDefault = $address->is_default;
        $user = $address->user;
        $address->delete();

        // If deleted address was default, make another one default
        if ($wasDefault) {
            $newDefault = $user->addresses()->latest()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت حذف شد.',
        ]);
    }

    /**
     * Set address as default.
     */
    public function setDefaultAddress(Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        $address->user->addresses()->where('is_default', true)->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'success' => true,
            'message' => 'آدرس پیشفرض تنظیم شد.',
            'address' => $address->fresh(),
        ]);
    }

    /**
     * Show orders list.
     */
    public function orders(): View
    {
        $user = Auth::user();
        $orders = Order::query()
            ->where('user_id', $user->id)
            ->with(['address', 'transactions' => fn ($q) => $q->latest()])
            ->latest('placed_at')
            ->paginate(10)
            ->withQueryString();

        return view('profile.orders', [
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    /**
     * Show order details. The policy prevents reading another user's order.
     */
    public function showOrder(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load([
            'address',
            'items.product.images',
            'transactions' => fn ($q) => $q->latest(),
        ]);

        return view('profile.order-detail', [
            'user' => Auth::user(),
            'order' => $order,
            'transaction' => $order->transactions->first(),
        ]);
    }

    /**
     * Validate and remember a discount code for the current cart.
     */
    public function applyDiscount(ApplyDiscountRequest $request): JsonResponse
    {
        $data = $request->validated();

        $cart = $this->cartService->resolve(false);
        $cart?->load(['items.product']);

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'سبد خرید شما خالی است.'], 422);
        }

        $subtotal = $cart->subtotal();
        $resolved = $this->discountService->resolve($data['code'] ?? null, $cart->items, $subtotal);

        if (! $resolved['discount']) {
            $request->session()->forget('discount_code');

            return response()->json([
                'success' => false,
                'message' => $resolved['message'] ?: 'کد تخفیف نامعتبر است.',
                'discount_amount' => 0,
                'total' => $subtotal,
            ], 422);
        }

        $request->session()->put('discount_code', $resolved['discount']->code);

        return response()->json([
            'success' => true,
            'message' => $resolved['message'],
            'discount_amount' => $resolved['amount'],
            'subtotal' => $subtotal,
            'total' => max($subtotal - $resolved['amount'], 0),
        ]);
    }

    /**
     * Show the checkout / place-order page for the current cart.
     */
    public function checkout(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $addresses = $user->addresses()->orderByDesc('is_default')->orderBy('created_at')->get();

        $cart = $this->cartService->resolve(false);
        $cart?->load(['items.product.images', 'items.product.brand', 'items.product.category']);

        $items = $cart?->items ?? collect();

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['cart' => 'سبد خرید شما خالی است.']);
        }

        $subtotal = $cart->subtotal();

        $discountCode = $request->query('discount_code')
            ?? $request->session()->get('discount_code');

        $resolved = $this->discountService->resolve($discountCode, $items, $subtotal);
        $discountAmount = $resolved['amount'];

        return view('profile.checkout', [
            'user' => $user,
            'addresses' => $addresses,
            'defaultAddress' => $user->getDefaultAddress(),
            'items' => $items,
            'subtotal' => $subtotal,
            'discountCode' => $resolved['discount']?->code ?? $discountCode,
            'discountAmount' => $discountAmount,
            'discountMessage' => $resolved['message'],
            'total' => max($subtotal - $discountAmount, 0),
        ]);
    }

    /**
     * Store a new order from the current cart for the authenticated user.
     */
    public function storeOrder(StoreOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $paymentMethod = $data['payment_method'] ?? 'gateway';
        $isCardToCard = $paymentMethod === 'card_to_card';

        $user = Auth::user();

        // The address must belong to the current user.
        $address = $user->addresses()->find($data['address_id']);
        if (! $address) {
            return back()->withErrors(['address_id' => 'آدرس انتخاب‌شده معتبر نیست.'])->withInput();
        }

        // Require name + last name if the profile is still incomplete.
        $needsName = blank($user->first_name) || blank($user->last_name);
        if ($needsName && (blank($data['first_name'] ?? null) || blank($data['last_name'] ?? null))) {
            return back()
                ->withErrors(['first_name' => 'لطفاً نام و نام خانوادگی خود را برای تکمیل سفارش وارد کنید.'])
                ->withInput();
        }

        $cart = $this->cartService->resolve(false);
        $cart?->load(['items.product']);

        if (! $cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->withErrors(['cart' => 'سبد خرید شما خالی است.']);
        }

        $subtotal = $cart->subtotal();
        $resolved = $this->discountService->resolve($data['discount_code'] ?? null, $cart->items, $subtotal);
        $discountAmount = $resolved['amount'];
        $total = max($subtotal - $discountAmount, 0);

        try {
            $order = DB::transaction(function () use ($user, $address, $cart, $data, $isCardToCard, $subtotal, $total, $resolved, $discountAmount) {
                // Re-check stock under lock to avoid overselling at checkout.
                foreach ($cart->items as $item) {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                    if (! $product || $item->quantity > $product->stock) {
                        throw new \RuntimeException('موجودی یکی از کالاها کافی نیست: '.($product?->name ?? 'نامشخص'));
                    }
                }

                // Persist any provided profile data.
                if (! empty($data['first_name'])) {
                    $user->first_name = $data['first_name'];
                }
                if (! empty($data['last_name'])) {
                    $user->last_name = $data['last_name'];
                }
                if ($user->isDirty(['first_name', 'last_name'])) {
                    $user->name = trim("{$user->first_name} {$user->last_name}");
                    $user->save();
                }

                $firstItem = $cart->items->first();

                $order = Order::create([
                    'number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                    'user_id' => $user->id,
                    'product_id' => $cart->items->count() === 1 ? $firstItem->product_id : null,
                    'address_id' => $address->id,
                    'customer_name' => $user->getFullNameAttribute(),
                    'shipping_first_name' => $user->first_name,
                    'shipping_last_name' => $user->last_name,
                    'phone' => $user->phone,
                    'shipping_phone' => $address->phone,
                    'size' => $cart->items->count() === 1 ? ($firstItem->size ?: null) : null,
                    'color' => $cart->items->count() === 1 ? ($firstItem->color ?: null) : null,
                    'subtotal' => $subtotal,
                    'discount_code' => $resolved['discount']?->code,
                    'discount_amount' => $discountAmount,
                    'total' => $total,
                    'status' => $isCardToCard ? 'pending_transaction' : 'pending',
                    'placed_at' => now(),
                ]);

                foreach ($cart->items as $item) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->name ?? 'نامشخص',
                        'size' => $item->size ?: null,
                        'color' => $item->color ?: null,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'line_total' => $item->unit_price * $item->quantity,
                    ]);
                }

                Transaction::create([
                    'order_id' => $order->id,
                    'user_id' => $user->id,
                    'amount' => $order->total,
                    'gateway' => $isCardToCard ? 'card' : 'zarinpal',
                    'payment_method' => $isCardToCard ? 'card_to_card' : 'gateway',
                    'tracking_code' => $isCardToCard ? null : Transaction::generateTrackingCode(),
                    'status' => 'pending',
                    'description' => $isCardToCard
                        ? 'در انتظار ثبت فیش کارت به کارت.'
                        : 'در انتظار پرداخت توسط کاربر.',
                ]);

                return $order;
            }, 3);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['cart' => $e->getMessage()])->withInput();
        }

        // Cart is consumed; empty it now that the order exists.
        $this->cartService->clear();
        $request->session()->forget('discount_code');

        if ($isCardToCard) {
            return redirect()
                ->route('profile.orders.receipt', $order)
                ->with('status', 'سفارش ثبت شد. لطفاً فیش واریزی را بارگذاری کنید.');
        }

        return redirect()
            ->route('profile.orders.show', $order)
            ->with('status', 'سفارش شما با موفقیت ثبت شد.');
    }

    /**
     * Show the card-to-card receipt upload page for an order.
     */
    public function showUploadReceipt(Order $order): View
    {
        $this->authorize('view', $order);

        $transaction = $order->transactions()->latest()->first();

        return view('profile.upload-receipt', [
            'user' => Auth::user(),
            'order' => $order,
            'transaction' => $transaction,
        ]);
    }

    /**
     * Store the uploaded card-to-card receipt and move the order to verification.
     */
    public function uploadReceipt(UploadReceiptRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();
        $file = $request->file('receipt_image');
        $extension = strtolower($file->extension());
        $extension = in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true) ? $extension : 'jpg';

        DB::transaction(function () use ($order, $data, $file, $extension) {
            $transaction = $order->transactions()->latest()->first()
                ?? $order->transactions()->make([
                    'user_id' => $order->user_id,
                    'amount' => $order->total,
                    'gateway' => 'card',
                    'payment_method' => 'card_to_card',
                ]);

            // Opaque filename: the user-supplied name is never used on disk.
            $path = $file->storeAs('receipts', Str::uuid().'.'.$extension, 'public');

            $transaction->fill([
                'receipt_image' => $path,
                'tracking_code' => $data['tracking_code'],
                'payment_method' => 'card_to_card',
                'gateway' => 'card',
                'status' => 'pending',
                'description' => 'فیش کارت به کارت بارگذاری شد و در انتظار تأیید است.',
            ]);
            $transaction->save();

            $order->update(['status' => 'pending_verification']);
        });

        return redirect()
            ->route('profile.orders.show', $order)
            ->with('status', 'فیش واریزی شما ثبت شد و در انتظار تأیید مدیر است.');
    }

    /**
     * Payment gateway callback / verification endpoint.
     *
     * Only a pending order with a pending transaction can be transitioned, so
     * a replayed callback cannot re-process an already settled order.
     */
    public function paymentCallback(PaymentCallbackRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        $hasPendingTransaction = $order->transactions()->where('status', 'pending')->exists();
        if ($order->status !== 'pending' || ! $hasPendingTransaction) {
            return back()->withErrors(['payment' => 'این سفارش قابل پردازش نیست.']);
        }

        $succeeded = $data['status'] === 'success';

        DB::transaction(function () use ($order, $data, $succeeded) {
            $transaction = $order->transactions()
                ->where('status', 'pending')
                ->latest()
                ->firstOrFail();

            $transaction->fill([
                'status' => $succeeded ? 'paid' : 'failed',
                'ref_id' => $data['ref_id'] ?? $transaction->ref_id,
                'card_number' => isset($data['card_number'])
                    ? Transaction::maskCard($data['card_number'])
                    : $transaction->card_number,
                'paid_at' => $succeeded ? now() : null,
                'description' => $succeeded
                    ? 'پرداخت با موفقیت تأیید شد.'
                    : 'پرداخت ناموفق یا لغو شده است.',
            ]);
            $transaction->save();

            $order->update(['status' => $succeeded ? 'processing' : 'cancelled']);
        });

        return redirect()
            ->route('profile.orders.show', $order)
            ->with('status', $succeeded
                ? 'پرداخت شما با موفقیت تأیید شد.'
                : 'پرداخت ناموفق بود. لطفاً دوباره تلاش کنید.');
    }

    /**
     * Show the account security page (password add/change/remove).
     */
    public function showSecurity(): View
    {
        return view('profile.security', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Set or change the user's password.
     */
    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        $hadPassword = filled($user->password);

        $user->password = $data['password'];
        $user->save();

        return redirect()
            ->route('profile.security')
            ->with('status', $hadPassword
                ? 'رمز عبور شما بهروزرسانی شد.'
                : 'رمز عبور برای حساب شما تنظیم شد.');
    }

    /**
     * Remove the user's password (login stays via OTP).
     */
    public function removePassword(RemovePasswordRequest $request): RedirectResponse
    {
        $request->validated();

        $user = Auth::user();
        $user->password = null;
        $user->save();

        return redirect()
            ->route('profile.security')
            ->with('status', 'رمز عبور حذف شد. از این پس با رمز یکبارمصرف وارد میشوید.');
    }

    /**
     * Update user profile info. Phone uniqueness is enforced by the FormRequest.
     */
    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->phone = $data['phone'];
        $user->name = trim("{$data['first_name']} {$data['last_name']}");
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'پروفایل با موفقیت بهروزرسانی شد.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
            ],
        ]);
    }
}
