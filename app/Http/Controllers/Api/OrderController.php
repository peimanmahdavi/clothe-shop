<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(private readonly CartService $cartService) {}

    public function index(Request $request): JsonResponse
    {
        $orders = $request->user()
            ->orders()
            ->with('address')
            ->latest('placed_at')
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $orders]);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order->load('address');

        return response()->json(['success' => true, 'data' => $order]);
    }

    /**
     * Create an order from the authenticated user's cart.
     *
     * The total is ALWAYS derived from server-side cart prices; a client can
     * no longer submit an arbitrary "total" to buy goods for free.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $address = $user->addresses()->find($data['address_id']);
        if (! $address) {
            return response()->json([
                'success' => false,
                'message' => 'آدرس انتخاب‌شده معتبر نیست.',
            ], 403);
        }

        $needsName = blank($user->first_name) || blank($user->last_name);
        if ($needsName && (blank($data['first_name'] ?? null) || blank($data['last_name'] ?? null))) {
            return response()->json([
                'success' => false,
                'message' => 'لطفاً نام و نام خانوادگی خود را برای تکمیل سفارش وارد کنید.',
                'needs_profile' => true,
            ], 422);
        }

        $cart = $this->cartService->resolve(false);
        $cart?->load('items.product');

        if (! $cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'سبد خرید شما خالی است.',
            ], 422);
        }

        $subtotal = $cart->subtotal();
        if ($subtotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'مبلغ سفارش معتبر نیست.',
            ], 422);
        }

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

        try {
            $order = DB::transaction(function () use ($user, $address, $cart, $subtotal) {
                foreach ($cart->items as $item) {
                    $product = Product::whereKey($item->product_id)->lockForUpdate()->first();

                    if (! $product || $item->quantity > $product->stock) {
                        throw new RuntimeException('موجودی یکی از کالاها کافی نیست.');
                    }
                }

                $order = Order::create([
                    'number' => 'ORD-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'customer_name' => $user->getFullNameAttribute(),
                    'shipping_first_name' => $user->first_name,
                    'shipping_last_name' => $user->last_name,
                    'phone' => $user->phone,
                    'shipping_phone' => $address->phone,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'status' => 'pending',
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

                $cart->items()->delete();

                return $order;
            }, 3);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'سفارش با موفقیت ثبت شد.',
            'data' => $order->load('address'),
        ], 201);
    }
}
