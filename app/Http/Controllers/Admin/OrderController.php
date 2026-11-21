<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectTransactionRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::query()
            ->with(['user', 'product', 'address', 'transactions' => fn ($q) => $q->latest()])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search')->trim().'%';
                $q->where(function ($q) use ($term) {
                    $q->where('number', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhere('phone', 'like', $term);
                });
            })
            ->latest('placed_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'statuses' => Order::STATUSES,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'product', 'address', 'transactions' => fn ($q) => $q->latest()]);

        return view('admin.orders.show', [
            'order' => $order,
            'statuses' => Order::STATUSES,
            'transaction' => $order->transactions->first(),
        ]);
    }

    public function approveTransaction(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $transaction = $order->transactions()->latest()->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'verified',
                    'paid_at' => $transaction->paid_at ?? now(),
                    'admin_note' => null,
                ]);
            }

            $order->update(['status' => 'processing']);
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'تراکنش تأیید شد و سفارش به مرحله پردازش رفت.');
    }

    public function rejectTransaction(RejectTransactionRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($order, $data) {
            $transaction = $order->transactions()->latest()->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'rejected',
                    'admin_note' => $data['admin_note'] ?? 'فیش ارسالی نامعتبر بود.',
                ]);
            }

            $order->update(['status' => 'pending_transaction']);
        });

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'فیش رد شد و سفارش به حالت انتظار ثبت فیش بازگشت.');
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();

        $order->update([
            'status' => $data['status'],
            'tracking_code' => $data['tracking_code'] ?? null,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'سفارش به‌روزرسانی شد.');
    }
}
