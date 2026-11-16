<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderController extends Controller
{
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

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'address_id' => ['required', 'integer', 'exists:addresses,id'],
            'total' => ['required', 'integer', 'min:0'],
            'first_name' => ['nullable', 'string', 'max:50'],
            'last_name' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $request->user();

        $address = $user->addresses()->find($request->address_id);
        if (!$address) {
            return response()->json([
                'success' => false,
                'message' => 'آدرس انتخاب‌شده معتبر نیست.',
            ], 403);
        }

        $needsName = blank($user->first_name) || blank($user->last_name);
        if ($needsName && (blank($request->first_name) || blank($request->last_name))) {
            return response()->json([
                'success' => false,
                'message' => 'لطفاً نام و نام خانوادگی خود را برای تکمیل سفارش وارد کنید.',
                'needs_profile' => true,
            ], 422);
        }

        if ($request->filled('first_name')) {
            $user->first_name = $request->first_name;
        }
        if ($request->filled('last_name')) {
            $user->last_name = $request->last_name;
        }
        if ($user->isDirty(['first_name', 'last_name'])) {
            $user->name = trim("{$user->first_name} {$user->last_name}");
            $user->save();
        }

        $order = Order::create([
            'number' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'user_id' => $user->id,
            'address_id' => $address->id,
            'customer_name' => $user->getFullNameAttribute(),
            'shipping_first_name' => $user->first_name,
            'shipping_last_name' => $user->last_name,
            'phone' => $user->phone,
            'shipping_phone' => $address->phone,
            'total' => $request->total,
            'status' => 'pending',
            'placed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'سفارش با موفقیت ثبت شد.',
            'data' => $order->load('address'),
        ], 201);
    }
}