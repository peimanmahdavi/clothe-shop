<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
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
    public function storeAddress(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:500'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'is_default' => ['boolean'],
        ]);

        $user = Auth::user();

        // If this is set as default, unset other defaults
        if ($request->boolean('is_default')) {
            $user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        // If this is the first address, make it default
        $isFirstAddress = $user->addresses()->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirstAddress;

        $address = $user->addresses()->create([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'province' => $request->province,
            'city' => $request->city,
            'address_line' => $request->address_line,
            'postal_code' => $request->postal_code,
            'is_default' => $isDefault,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت اضافه شد.',
            'address' => $address->load('user'),
        ]);
    }

    /**
     * Update an address.
     */
    public function updateAddress(Request $request, Address $address): JsonResponse
    {
        $this->authorize('update', $address);

        $request->validate([
            'title' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
            'province' => ['required', 'string', 'max:100'],
            'city' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:500'],
            'postal_code' => ['nullable', 'string', 'regex:/^\d{10}$/'],
            'is_default' => ['boolean'],
        ]);

        // If this is set as default, unset other defaults
        if ($request->boolean('is_default') && !$address->is_default) {
            $address->user->addresses()->where('is_default', true)->update(['is_default' => false]);
        }

        $address->update([
            'title' => $request->title,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'province' => $request->province,
            'city' => $request->city,
            'address_line' => $request->address_line,
            'postal_code' => $request->postal_code,
            'is_default' => $request->boolean('is_default'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'آدرس با موفقیت به‌روزرسانی شد.',
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
            'message' => 'آدرس پیش‌فرض تنظیم شد.',
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
     * Show order details.
     */
    public function showOrder(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load('address');

        return view('profile.order-detail', [
            'user' => Auth::user(),
            'order' => $order,
        ]);
    }

    /**
     * Show the checkout / place-order page.
     */
    public function checkout(Request $request): View
    {
        $user = Auth::user();
        $addresses = $user->addresses()->orderByDesc('is_default')->orderBy('created_at')->get();

        $product = null;
        if ($productId = $request->query('product_id')) {
            $product = Product::with('images')->find($productId);
        }

        return view('profile.checkout', [
            'user' => $user,
            'addresses' => $addresses,
            'defaultAddress' => $user->getDefaultAddress(),
            'product' => $product,
            'selectedSize' => $request->query('size'),
            'selectedColor' => $request->query('color'),
        ]);
    }

    /**
     * Store a new order for the authenticated user.
     */
    public function storeOrder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'address_id' => ['required', 'integer'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'size' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
            'payment_method' => ['nullable', 'in:gateway,card_to_card'],
        ]);

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

        $product = ! empty($data['product_id']) ? Product::find($data['product_id']) : null;

        $order = DB::transaction(function () use ($user, $address, $product, $data, $isCardToCard) {
            $order = Order::create([
                'number' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
                'user_id' => $user->id,
                'product_id' => $product?->id,
                'address_id' => $address->id,
                'customer_name' => $user->getFullNameAttribute(),
                'shipping_first_name' => $user->first_name,
                'shipping_last_name' => $user->last_name,
                'phone' => $user->phone,
                'shipping_phone' => $address->phone,
                'size' => $data['size'] ?? null,
                'color' => $data['color'] ?? null,
                'total' => $product?->finalPrice() ?? 0,
                'status' => $isCardToCard ? 'pending_transaction' : 'pending',
                'placed_at' => now(),
            ]);

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
        });

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
    public function uploadReceipt(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('view', $order);

        $data = $request->validate([
            'tracking_code' => ['required', 'string', 'max:100'],
            'receipt_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        DB::transaction(function () use ($order, $data) {
            $transaction = $order->transactions()->latest()->first()
                ?? $order->transactions()->make([
                    'user_id' => $order->user_id,
                    'amount' => $order->total,
                    'gateway' => 'card',
                    'payment_method' => 'card_to_card',
                ]);

            $path = request()->file('receipt_image')->store('receipts', 'public');

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
     * Called by the gateway after the user returns from payment. Verifies the
     * payment, then creates or updates the linked Transaction and Order state
     * atomically so the ledger never drifts from the order.
     */
    public function paymentCallback(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('view', $order);

        $data = $request->validate([
            'status' => ['required', 'in:success,failed'],
            'ref_id' => ['nullable', 'string', 'max:191'],
            'card_number' => ['nullable', 'string', 'max:32'],
            'gateway' => ['nullable', 'string', 'max:50'],
        ]);

        $succeeded = $data['status'] === 'success';

        DB::transaction(function () use ($order, $data, $succeeded) {
            $transaction = $order->transactions()
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (! $transaction) {
                $transaction = $order->transactions()->make([
                    'user_id' => $order->user_id,
                    'amount' => $order->total,
                    'gateway' => $data['gateway'] ?? 'zarinpal',
                    'tracking_code' => Transaction::generateTrackingCode(),
                ]);
            }

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

            if ($succeeded) {
                $order->update(['status' => 'processing']);
            } else {
                $order->update(['status' => 'cancelled']);
            }
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
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        // Require the current password only when one already exists.
        if (filled($user->password)) {
            $rules['current_password'] = ['required', 'current_password'];
        }

        $data = $request->validate($rules);
        $hadPassword = filled($user->password);

        $user->password = $data['password'];
        $user->save();

        return redirect()
            ->route('profile.security')
            ->with('status', $hadPassword
                ? 'رمز عبور شما به‌روزرسانی شد.'
                : 'رمز عبور برای حساب شما تنظیم شد.');
    }

    /**
     * Remove the user's password (login stays via OTP).
     */
    public function removePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'current_password' => ['required', 'current_password'],
        ]);

        $user->password = null;
        $user->save();

        return redirect()
            ->route('profile.security')
            ->with('status', 'رمز عبور حذف شد. از این پس با رمز یک‌بارمصرف وارد می‌شوید.');
    }

    /**
     * Update user profile info.
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'regex:/^09\d{9}$/'],
        ]);

        $user = Auth::user();
        
        // Check if phone is already taken by another user
        if ($request->phone !== $user->phone) {
            $exists = User::where('phone', $request->phone)->where('id', '!=', $user->id)->exists();
            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'این شماره تلفن قبلاً ثبت شده است.',
                ], 422);
            }
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->phone = $request->phone;
        $user->name = trim("{$request->first_name} {$request->last_name}");
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'پروفایل با موفقیت به‌روزرسانی شد.',
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