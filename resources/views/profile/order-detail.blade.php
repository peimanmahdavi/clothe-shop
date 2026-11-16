@extends('layouts.app')

@section('title', 'سفارش #{{ $order->number }} | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
    @endphp

    <section class="py-8">
        <div class="container-page">
            <div class="mx-auto max-w-3xl">
                @if (session('status'))
                    <div class="mb-6 flex items-center gap-3 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700" role="status">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m5 13 4 4L19 7" />
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Header --}}
                <div class="mb-8">
                    <div class="flex flex-col items-start gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <a href="{{ route('profile.orders') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink mb-2">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M10 6 6 10l4 4" />
                                </svg>
                                برگشت به سفارش‌ها
                            </a>
                            <h1 class="text-2xl font-extrabold text-ink">جزئیات سفارش</h1>
                            <p class="mt-1 text-sm text-muted">شماره سفارش: <span class="font-mono font-bold text-ink" dir="ltr">#{{ $order->number }}</span></p>
                        </div>
                        @php
                            $statusClasses = [
                                'pending' => 'bg-amber-100 text-amber-700',
                                'processing' => 'bg-brand-soft text-brand-deep',
                                'shipped' => 'bg-blue-100 text-blue-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="status-pill shrink-0 {{ $statusClasses[$order->status] ?? 'bg-canvas text-muted' }}">
                            {{ $order->statusLabel() }}
                        </span>
                    </div>
                </div>

                {{-- Order Info Grid --}}
                <div class="grid gap-4 lg:grid-cols-2 mb-8">
                    {{-- Shipping Address --}}
                    <div class="admin-card p-5">
                        <h3 class="font-bold text-ink flex items-center gap-2">
                            <svg class="size-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" />
                                <circle cx="12" cy="10" r="3" />
                            </svg>
                            آدرس تحویل
                        </h3>
                        @if ($order->address)
                            <div class="mt-3 space-y-1 text-sm">
                                <p class="font-medium text-ink">{{ $order->shipping_full_name }}</p>
                                <p class="text-muted">{{ $order->shipping_phone }}</p>
                                <p class="text-muted">{{ $order->address->full_address }}</p>
                                @if ($order->address->postal_code)
                                    <p class="text-muted">کد پستی: {{ $toman($order->address->postal_code) }}</p>
                                @endif
                            </div>
                        @else
                            <div class="mt-3 space-y-1 text-sm text-muted">
                                <p class="font-medium text-ink">{{ $order->shipping_full_name }}</p>
                                <p>{{ $order->shipping_phone }}</p>
                                <p>آدرس کامل در سیستم موجود نیست</p>
                            </div>
                        @endif
                    </div>

                    {{-- Order Summary --}}
                    <div class="admin-card p-5">
                        <h3 class="font-bold text-ink flex items-center gap-2">
                            <svg class="size-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <polyline points="10 9 9 9 8 9" />
                            </svg>
                            خلاصه سفارش
                        </h3>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <dt class="text-muted">تاریخ ثبت</dt>
                                <dd class="font-medium text-ink">{{ $toPersian($order->placed_at->format('Y/m/d H:i')) }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-muted">مجموع کالاها</dt>
                                <dd class="font-medium text-ink">{{ $toman($order->total) }} تومان</dd>
                            </div>
                            <div class="flex justify-between border-t border-line pt-3">
                                <dt class="text-muted font-bold">مبلغ قابل پرداخت</dt>
                                <dd class="font-bold text-brand-deep text-lg">{{ $toman($order->total) }} تومان</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Order Items (placeholder - would need order_items table) --}}
                <div class="admin-card overflow-hidden">
                    <div class="p-5 border-b border-line">
                        <h3 class="font-bold text-ink flex items-center gap-2">
                            <svg class="size-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 8h12l-1 12H7L6 8Z" />
                                <path d="M9 8a3 3 0 0 1 6 0" />
                            </svg>
                            اقلام سفارش
                        </h3>
                    </div>
                    <div class="p-5 text-center text-muted">
                        <p class="font-medium text-ink">لیست اقلام سفارش</p>
                        <p class="mt-1 text-sm">برای نمایش اقلام، جدول order_items مورد نیاز است.</p>
                    </div>
                </div>

                {{-- Status Timeline --}}
                <div class="mt-8 admin-card overflow-hidden">
                    <div class="p-5 border-b border-line">
                        <h3 class="font-bold text-ink">زمان‌بندی وضعیت</h3>
                    </div>
                    <div class="p-5">
                        <div class="relative pl-6 border-r border-line">
                            @php
                                $statuses = [
                                    'pending' => ['label' => 'ثبت سفارش', 'time' => $order->placed_at, 'active' => true],
                                    'processing' => ['label' => 'در حال پردازش', 'time' => $order->status === 'processing' || in_array($order->status, ['shipped', 'completed']) ? $order->updated_at : null, 'active' => in_array($order->status, ['processing', 'shipped', 'completed'])],
                                    'shipped' => ['label' => 'ارسال شده', 'time' => in_array($order->status, ['shipped', 'completed']) ? $order->updated_at : null, 'active' => in_array($order->status, ['shipped', 'completed'])],
                                    'completed' => ['label' => 'تکمیل شده', 'time' => $order->status === 'completed' ? $order->updated_at : null, 'active' => $order->status === 'completed'],
                                    'cancelled' => ['label' => 'لغو شده', 'time' => $order->status === 'cancelled' ? $order->updated_at : null, 'active' => $order->status === 'cancelled'],
                                ];
                            @endphp
                            
                            @foreach ($statuses as $key => $status)
                                <div class="relative pb-8 last:pb-0">
                                    <div class="absolute -start-3 top-0 size-6 rounded-full border-2 border-line bg-white flex items-center justify-center z-10
                                        @if ($status['active']) border-brand bg-brand @else border-line bg-white @endif
                                    ">
                                        @if ($status['active'])
                                            <svg class="size-3.5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M20 6 9 17l-5-5" />
                                            </svg>
                                        @else
                                            <span class="size-1.5 rounded-full bg-line"></span>
                                        @endif
                                    </div>
                                    <div class="ml-2">
                                        <p class="font-medium text-ink {{ $status['active'] ? 'text-brand-deep' : '' }}">{{ $status['label'] }}</p>
                                        @if ($status['time'])
                                            <p class="text-sm text-muted mt-0.5">{{ $toPersian($status['time']->format('Y/m/d H:i')) }}</p>
                                        @else
                                            <p class="text-sm text-muted mt-0.5">—</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection