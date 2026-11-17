@extends('layouts.app')

@section('title', 'سفارش‌های من | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
        $statusClasses = [
            'pending_transaction' => 'bg-amber-100 text-amber-700',
            'pending_verification' => 'bg-sky-100 text-sky-700',
            'pending' => 'bg-amber-100 text-amber-700',
            'processing' => 'bg-brand-soft text-brand-deep',
            'shipped' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
        ];
    @endphp

    <section class="py-8">
        <div class="container-page">
            <div class="mx-auto max-w-5xl">
                {{-- Header --}}
                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-ink">سفارش‌های من</h1>
                    <p class="mt-1 text-sm text-muted">مشاهده و پیگیری تمام سفارش‌های شما</p>
                </div>

                {{-- Orders Table --}}
                <div class="admin-card overflow-hidden">
                    @if ($orders->isEmpty())
                        <div class="p-10 text-center">
                            <div class="flex size-16 mx-auto items-center justify-center rounded-xl bg-canvas text-muted mb-3">
                                <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 8h12l-1 12H7L6 8Z" />
                                    <path d="M9 8a3 3 0 0 1 6 0" />
                                </svg>
                            </div>
                            <h3 class="font-bold text-ink">هنوز سفارشی ثبت نکرده‌اید</h3>
                            <p class="mt-1 text-sm text-muted">وقتی سفارشی ثبت کنید، در اینجا نمایش داده می‌شود</p>
                            <a href="{{ route('home') }}" class="btn btn-primary mt-4 inline-flex">مشاهده محصولات</a>
                        </div>
                    @else
                        {{-- Responsive: 3 compact columns on mobile, full 6 columns on desktop --}}
                        <table class="w-full text-sm">
                            <thead class="bg-canvas/70 text-[11px] text-muted">
                                <tr>
                                    <th class="px-4 py-3 text-start font-bold md:hidden">سفارش</th>
                                    <th class="hidden px-5 py-3 text-start font-bold md:table-cell">شماره سفارش</th>
                                    <th class="hidden px-3 py-3 text-start font-bold md:table-cell">تاریخ</th>
                                    <th class="hidden px-3 py-3 text-start font-bold md:table-cell">آدرس تحویل</th>
                                    <th class="px-3 py-3 text-start font-bold">مبلغ کل</th>
                                    <th class="hidden px-3 py-3 text-start font-bold md:table-cell">وضعیت</th>
                                    <th class="px-4 py-3 text-end font-bold md:px-5">عملیات</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @foreach ($orders as $order)
                                    <tr class="transition-colors hover:bg-canvas/60">
                                        {{-- Mobile: Order & Date + compact status badge --}}
                                        <td class="px-4 py-3 md:hidden">
                                            <p class="font-mono text-[13px] font-bold text-ink" dir="ltr">#{{ $order->number }}</p>
                                            <p class="mt-0.5 text-[11px] text-muted">{{ $toPersian(\App\Support\Jalali::format($order->placed_at)) }}</p>
                                            <span class="status-pill mt-1.5 {{ $statusClasses[$order->status] ?? 'bg-canvas text-muted' }}">{{ $order->statusLabel() }}</span>
                                        </td>
                                        {{-- Desktop: Order number --}}
                                        <td class="hidden px-5 py-3 md:table-cell">
                                            <p class="font-bold text-ink font-mono" dir="ltr">{{ $order->number }}</p>
                                        </td>
                                        {{-- Desktop: Date --}}
                                        <td class="hidden px-3 py-3 text-xs text-muted md:table-cell">{{ $toPersian(\App\Support\Jalali::format($order->placed_at)) }}</td>
                                        {{-- Desktop: Delivery address (hidden on mobile) --}}
                                        <td class="hidden px-3 py-3 md:table-cell">
                                            @if ($order->address)
                                                <p class="font-medium text-ink">{{ $order->address->title }}</p>
                                                <p class="text-[11px] text-muted truncate max-w-xs">{{ $order->address->full_address }}</p>
                                            @else
                                                <p class="text-sm text-muted">آدرس مشخص نشده</p>
                                            @endif
                                        </td>
                                        {{-- Amount (always visible) --}}
                                        <td class="px-3 py-3">
                                            <p class="whitespace-nowrap font-bold text-ink">{{ $toman($order->total) }} <span class="text-[10px] font-medium text-muted">تومان</span></p>
                                        </td>
                                        {{-- Desktop: Status --}}
                                        <td class="hidden px-3 py-3 md:table-cell">
                                            <span class="status-pill {{ $statusClasses[$order->status] ?? 'bg-canvas text-muted' }}">{{ $order->statusLabel() }}</span>
                                        </td>
                                        {{-- Actions (always visible, pinned to left edge in RTL) --}}
                                        <td class="px-4 py-3 md:px-5">
                                            <div class="flex flex-col items-stretch gap-1.5 md:flex-row md:items-center md:justify-end">
                                                @if ($order->status === 'pending_transaction')
                                                    <a href="{{ route('profile.orders.receipt', $order) }}" class="inline-flex items-center justify-center gap-1 rounded-lg bg-amber-500 px-2.5 py-1.5 text-[11px] font-bold text-white shadow-sm shadow-amber-500/30 transition hover:bg-amber-600">
                                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5"/><path d="M5 16v3h14v-3"/></svg>
                                                        ثبت فیش
                                                    </a>
                                                @endif
                                                <a href="{{ route('profile.orders.show', $order) }}" class="inline-flex items-center justify-center gap-1 rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">جزئیات</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Pagination --}}
                        <div class="p-4 border-t border-line">
                            {{ $orders->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection