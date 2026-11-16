@extends('layouts.app')

@section('title', 'سفارش‌های من | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
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
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[700px] text-sm">
                                <thead class="bg-canvas/70 text-[11px] text-muted">
                                    <tr>
                                        <th class="px-5 py-3 text-start font-bold">شماره سفارش</th>
                                        <th class="px-3 py-3 text-start font-bold">تاریخ</th>
                                        <th class="px-3 py-3 text-start font-bold">آدرس تحویل</th>
                                        <th class="px-3 py-3 text-start font-bold">مبلغ</th>
                                        <th class="px-3 py-3 text-start font-bold">وضعیت</th>
                                        <th class="px-5 py-3 text-end font-bold">عملیات</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @foreach ($orders as $order)
                                        <tr class="transition-colors hover:bg-canvas/60">
                                            <td class="px-5 py-3">
                                                <p class="font-bold text-ink font-mono" dir="ltr">{{ $order->number }}</p>
                                            </td>
                                            <td class="px-3 py-3 text-xs text-muted">{{ $toPersian($order->placed_at->format('Y/m/d H:i')) }}</td>
                                            <td class="px-3 py-3">
                                                @if ($order->address)
                                                    <p class="font-medium text-ink">{{ $order->address->title }}</p>
                                                    <p class="text-[11px] text-muted truncate max-w-xs">{{ $order->address->full_address }}</p>
                                                @else
                                                    <p class="text-sm text-muted">آدرس مشخص نشده</p>
                                                @endif
                                            </td>
                                            <td class="px-3 py-3 font-bold text-ink">{{ $toPersian(number_format($order->total)) }} تومان</td>
                                            <td class="px-3 py-3">
                                                @php
                                                    $statusClasses = [
                                                        'pending_transaction' => 'bg-amber-100 text-amber-700',
                                                        'pending_verification' => 'bg-amber-100 text-amber-700',
                                                        'pending' => 'bg-amber-100 text-amber-700',
                                                        'processing' => 'bg-brand-soft text-brand-deep',
                                                        'shipped' => 'bg-blue-100 text-blue-700',
                                                        'completed' => 'bg-green-100 text-green-700',
                                                        'cancelled' => 'bg-red-100 text-red-700',
                                                    ];
                                                @endphp
                                                <span class="status-pill {{ $statusClasses[$order->status] ?? 'bg-canvas text-muted' }}">
                                                    {{ $order->statusLabel() }}
                                                </span>
                                                @if ($order->status === 'pending_transaction')
                                                    <p class="mt-1 text-[10px] font-bold text-accent">در انتظار ثبت فیش کارت به کارت</p>
                                                @endif
                                            </td>
                                            <td class="px-5 py-3">
                                                <div class="flex flex-col items-end gap-1.5">
                                                    @if ($order->status === 'pending_transaction')
                                                        <a href="{{ route('profile.orders.receipt', $order) }}" class="inline-flex items-center gap-1 rounded-lg bg-accent px-2.5 py-1.5 text-[11px] font-bold text-white shadow-sm shadow-accent/30 transition hover:bg-accent-deep">
                                                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4M7 9l5-5 5 5"/><path d="M5 16v3h14v-3"/></svg>
                                                            آپلود فیش واریزی
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('profile.orders.show', $order) }}" class="rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">جزئیات</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

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