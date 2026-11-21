<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateTransactionRequest;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $query = Transaction::query()->with(['order', 'user']);

        // Search by tracking code, order number, customer name or phone.
        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.$request->string('search')->trim().'%';
            $q->where(function ($q) use ($term) {
                $q->where('tracking_code', 'like', $term)
                    ->orWhere('ref_id', 'like', $term)
                    ->orWhereHas('order', function ($q) use ($term) {
                        $q->where('number', 'like', $term)
                            ->orWhere('customer_name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    })
                    ->orWhereHas('user', function ($q) use ($term) {
                        $q->where('name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    });
            });
        });

        $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));
        $query->when($request->filled('gateway'), fn ($q) => $q->where('gateway', $request->string('gateway')));

        // Date range filter (raw Y-m-d from the date inputs, Jalali shown in UI).
        $query->when($request->filled('from'), fn ($q) => $q->whereDate('created_at', '>=', $request->date('from')));
        $query->when($request->filled('to'), fn ($q) => $q->whereDate('created_at', '<=', $request->date('to')));

        $transactions = $query->latest('created_at')->paginate(15)->withQueryString();

        $todayStart = Carbon::today();

        $kpi = [
            'revenue' => (int) Transaction::query()->paid()->sum('amount'),
            'today' => Transaction::query()->where('created_at', '>=', $todayStart)->count(),
            'today_revenue' => (int) Transaction::query()->paid()->where('paid_at', '>=', $todayStart)->sum('amount'),
            'failed_pending' => Transaction::query()->whereIn('status', ['failed', 'pending'])->count(),
            'refunded' => (int) Transaction::query()->where('status', 'refunded')->sum('amount'),
        ];

        return view('admin.transactions.index', [
            'transactions' => $transactions,
            'kpi' => $kpi,
            'statuses' => Transaction::STATUSES,
            'gateways' => Transaction::GATEWAYS,
            'filters' => $request->only(['search', 'status', 'gateway', 'from', 'to']),
        ]);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $data = $request->validated();

        $transaction->update([
            'status' => $data['status'],
            'description' => $data['description'] ?? $transaction->description,
            'paid_at' => $data['status'] === 'paid' ? ($transaction->paid_at ?? now()) : $transaction->paid_at,
        ]);

        return redirect()
            ->route('admin.transactions.index')
            ->with('status', 'وضعیت تراکنش به‌روزرسانی شد.');
    }
}
