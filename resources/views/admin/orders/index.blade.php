@extends('layouts.admin')

@section('title', 'سفارش‌ها | پنل مدیریت')
@section('breadcrumb', 'سفارش‌ها')
@section('page-title', 'مدیریت سفارش‌ها')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $statusClasses = [
            'pending' => 'bg-amber-100 text-amber-700',
            'processing' => 'bg-brand-soft text-brand-deep',
            'shipped' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="admin-card mb-4 p-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="field" placeholder="جست‌وجو با شماره سفارش، نام یا موبایل">
            </div>
            <div>
                <select name="status" class="field" onchange="this.form.submit()">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <div class="admin-card overflow-hidden">
        @if ($orders->isEmpty())
            <div class="p-10 text-center text-sm text-muted">سفارشی یافت نشد.</div>
        @else
            {{-- Mobile compact cards --}}
            <div class="divide-y divide-line md:hidden">
                @foreach ($orders as $order)
                    @php($trx = $order->transactions->first())
                    <div class="flex items-center gap-3 p-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-ink" dir="ltr">{{ $order->number }}</span>
                                <span class="status-pill {{ $statusClasses[$order->status] ?? 'bg-canvas text-muted' }}">{{ $order->statusLabel() }}</span>
                            </div>
                            <p class="mt-1 truncate text-sm font-semibold text-ink">{{ $order->customer_name }}</p>
                            <div class="mt-1 flex items-center gap-1 text-xs font-bold text-ink">
                                {{ $toPersian(number_format($order->total)) }} <span class="text-[10px] font-medium text-muted">تومان</span>
                                @if ($trx && $trx->hasReceipt())
                                    <svg class="size-3.5 text-brand-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="فیش پیوست"><path d="M6 3.5h12a1 1 0 0 1 1 1V20l-2.5-1.5L14 20l-2-1.5L10 20l-2.5-1.5L5 20V4.5a1 1 0 0 1 1-1Z"/></svg>
                                @endif
                            </div>
                        </div>
                        <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-line bg-white px-2.5 py-2 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                            بررسی
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- Desktop full table --}}
            <div class="hidden md:block">
                <table class="w-full text-sm">
                    <thead class="bg-canvas/70 text-[11px] text-muted">
                        <tr>
                            <th class="px-4 py-3 text-start font-bold">شماره</th>
                            <th class="px-3 py-3 text-start font-bold">مشتری و موبایل</th>
                            <th class="px-3 py-3 text-start font-bold">محصول</th>
                            <th class="px-3 py-3 text-start font-bold">مبلغ</th>
                            <th class="px-3 py-3 text-start font-bold">تراکنش / روش پرداخت</th>
                            <th class="px-3 py-3 text-start font-bold">وضعیت</th>
                            <th class="px-3 py-3 text-start font-bold">تاریخ</th>
                            <th class="px-4 py-3 text-end font-bold">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($orders as $order)
                            @php($trx = $order->transactions->first())
                            <tr class="transition-colors hover:bg-canvas/60">
                                <td class="px-4 py-3"><span class="font-mono font-bold text-ink" dir="ltr">{{ $order->number }}</span></td>
                                <td class="px-3 py-3">
                                    <p class="font-medium text-ink">{{ $order->customer_name }}</p>
                                    <p class="text-[11px] text-muted" dir="ltr">{{ $order->phone }}</p>
                                </td>
                                <td class="px-3 py-3">
                                    @if ($order->product)
                                        <p class="font-medium text-ink">{{ $order->product->name }}</p>
                                        <p class="text-[11px] text-muted">
                                            @if ($order->size) سایز {{ $order->size }} @endif
                                            @if ($order->color) • {{ $order->color }} @endif
                                        </p>
                                    @else
                                        <span class="text-[11px] text-muted">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 font-bold text-ink">{{ $toPersian(number_format($order->total)) }}</td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    @if ($trx)
                                        <span class="badge-soft">{{ $trx->paymentMethodLabel() }}</span>
                                        @if ($trx->hasReceipt())
                                            <span class="ms-1 inline-flex align-middle text-brand-deep" title="فیش پیوست">
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h12a1 1 0 0 1 1 1V20l-2.5-1.5L14 20l-2-1.5L10 20l-2.5-1.5L5 20V4.5a1 1 0 0 1 1-1Z"/></svg>
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-[11px] text-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="status-pill {{ $statusClasses[$order->status] ?? 'bg-canvas text-muted' }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-xs text-muted">{{ $toPersian(\App\Support\Jalali::format($order->placed_at, 'Y/m/d')) }}</td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">ویرایش / بررسی</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-line p-4">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
