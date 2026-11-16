@extends('layouts.admin')

@section('title', 'داشبورد | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'داشبورد')

@section('page-title', 'داشبورد فروشگاه')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰',
            '1' => '۱',
            '2' => '۲',
            '3' => '۳',
            '4' => '۴',
            '5' => '۵',
            '6' => '۶',
            '7' => '۷',
            '8' => '۸',
            '9' => '۹',
            ',' => '٬',
        ]);

        $toman = static fn (int $amount): string => $toPersian(number_format($amount));

        $statusStyles = [
            'pending_transaction' => 'bg-amber-100 text-amber-700',
            'pending_verification' => 'bg-amber-100 text-amber-700',
            'pending' => 'bg-accent-soft text-accent',
            'processing' => 'bg-brand-soft text-brand-deep',
            'shipped' => 'bg-brand-soft/60 text-brand-deep',
            'completed' => 'bg-canvas text-ink',
            'cancelled' => 'bg-canvas text-muted',
        ];

        $statusBars = [
            'pending_transaction' => 'bg-amber-400',
            'pending_verification' => 'bg-amber-500',
            'pending' => 'bg-accent',
            'processing' => 'bg-brand-strong',
            'shipped' => 'bg-brand',
            'completed' => 'bg-ink/70',
            'cancelled' => 'bg-muted/40',
        ];

        $stats = [
            ['label' => 'فروش امروز', 'value' => $toman($salesToday), 'unit' => 'تومان', 'delta' => null, 'hint' => $toPersian($ordersToday).' سفارش امروز', 'icon' => 'wallet'],
            ['label' => 'فروش ۷ روز اخیر', 'value' => $toman($salesWeek), 'unit' => 'تومان', 'delta' => $weekGrowth, 'hint' => 'نسبت به هفته گذشته', 'icon' => 'chart'],
            ['label' => 'فروش ۳۰ روز اخیر', 'value' => $toman($salesMonth), 'unit' => 'تومان', 'delta' => $monthGrowth, 'hint' => 'نسبت به ماه گذشته', 'icon' => 'calendar'],
            ['label' => 'سفارش‌های در انتظار', 'value' => $toPersian($pendingCount), 'unit' => 'سفارش', 'delta' => null, 'hint' => 'به ارزش '.$toman($pendingTotal).' تومان', 'icon' => 'clock', 'urgent' => true],
        ];

        $chartTotals = collect($chart)->pluck('total');
        $chartMax = max((int) $chartTotals->max(), 1) * 1.12;
        $chartWidth = 640;
        $chartBaseline = 200;
        $chartTop = 20;
        $chartStep = $chartWidth / max(count($chart) - 1, 1);
        $chartPoints = [];

        foreach ($chartTotals as $index => $total) {
            $pointX = round($index * $chartStep, 1);
            $pointY = round($chartBaseline - ($total / $chartMax) * ($chartBaseline - $chartTop), 1);
            $chartPoints[] = $pointX.','.$pointY;
        }

        $linePath = 'M'.implode(' L', $chartPoints);
        $areaPath = $linePath.' L'.$chartWidth.','.$chartBaseline.' L0,'.$chartBaseline.' Z';
    @endphp

    @if ($pendingCount > 0 || $outOfStock > 0)
        <div class="mb-5 flex flex-col gap-3 rounded-2xl border border-red-100 bg-accent-soft px-4 py-3 sm:flex-row sm:items-center">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-accent text-white shadow-lg shadow-accent/30">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 8v5M12 17h.01" />
                    <path d="M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                </svg>
            </span>
            <p class="text-xs font-semibold leading-6 text-ink sm:text-sm">
                {{ $toPersian($pendingCount) }} سفارش در انتظار پرداخت و {{ $toPersian($outOfStock) }} محصول ناموجود است.
                <span class="font-bold text-accent">رسیدگی فوری لازم است.</span>
            </p>
            <a href="#recent-orders" class="btn btn-accent btn-sm w-full sm:ms-auto sm:w-auto">بررسی سفارش‌ها</a>
        </div>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">داشبورد فروشگاه</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">فروش امروز، هفته و ماه گذشته، سفارش‌های در انتظار و وضعیت انبار.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge-soft">{{ $toPersian($ordersCount) }} سفارش ثبت‌شده</span>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                افزودن محصول
            </a>
        </div>
    </div>

    <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="stat-card">
                <span class="pointer-events-none absolute -end-8 -top-8 size-24 rounded-full {{ ($stat['urgent'] ?? false) ? 'bg-accent-soft' : 'bg-brand-soft/70' }}" aria-hidden="true"></span>

                <div class="relative flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-muted">{{ $stat['label'] }}</p>
                        <p class="mt-2 truncate text-lg font-extrabold text-ink sm:text-xl">
                            {{ $stat['value'] }}
                            <span class="text-[11px] font-medium text-muted">{{ $stat['unit'] }}</span>
                        </p>
                    </div>

                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ ($stat['urgent'] ?? false) ? 'bg-accent-soft text-accent' : 'bg-brand-soft text-brand-deep' }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            @switch($stat['icon'])
                                @case('wallet')
                                    <path d="M3.5 8.5A2.5 2.5 0 0 1 6 6h12a2.5 2.5 0 0 1 2.5 2.5v7A2.5 2.5 0 0 1 18 18H6a2.5 2.5 0 0 1-2.5-2.5v-7Z" />
                                    <path d="M3.5 10.5h17" />
                                    <circle cx="16.5" cy="14.5" r="1.2" />
                                    @break
                                @case('chart')
                                    <path d="M4 20V10M10 20V5M16 20v-7M22 20H2" />
                                    @break
                                @case('calendar')
                                    <rect x="3.5" y="5" width="17" height="15" rx="2.5" />
                                    <path d="M8 3.5v3M16 3.5v3M3.5 10h17" />
                                    @break
                                @default
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 7v5l3 2" />
                            @endswitch
                        </svg>
                    </span>
                </div>

                <div class="relative mt-3 flex items-center gap-1.5 text-[11px]">
                    @if ($stat['delta'] !== null)
                        <span @class([
                            'rounded-full px-2 py-0.5 font-extrabold',
                            'bg-brand-soft text-brand-deep' => $stat['delta'] >= 0,
                            'bg-accent-soft text-accent' => $stat['delta'] < 0,
                        ])>
                            {{ $stat['delta'] >= 0 ? '+' : '−' }}{{ $toPersian(abs($stat['delta'])) }}٪
                        </span>
                    @endif
                    <span @class(['text-muted', 'font-bold text-accent' => $stat['urgent'] ?? false])>{{ $stat['hint'] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <section class="admin-card p-5 lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-ink">فروش ۱۲ روز گذشته</h2>
                    <p class="mt-1 text-[11px] text-muted">مجموع فروش روزانه، به تومان</p>
                </div>
                <span class="rounded-full bg-brand-soft px-2.5 py-1 text-[11px] font-extrabold text-brand-deep">{{ $toman((int) $chartTotals->sum()) }} تومان</span>
            </div>

            <div class="mt-4 overflow-x-auto">
                <svg viewBox="0 0 640 220" class="h-52 w-full min-w-[420px] sm:h-56" role="img" aria-label="نمودار فروش ۱۲ روز گذشته">
                    <defs>
                        <linearGradient id="salesFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#38BDF8" stop-opacity="0.38" />
                            <stop offset="1" stop-color="#38BDF8" stop-opacity="0" />
                        </linearGradient>
                        <linearGradient id="salesLine" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0" stop-color="#38BDF8" />
                            <stop offset="1" stop-color="#0EA5E9" />
                        </linearGradient>
                    </defs>

                    @foreach ([20, 65, 110, 155, 200] as $gridY)
                        <line x1="0" y1="{{ $gridY }}" x2="640" y2="{{ $gridY }}" stroke="#E2E8F0" stroke-width="1" stroke-dasharray="4 6" />
                    @endforeach

                    <path d="{{ $areaPath }}" fill="url(#salesFill)" />
                    <path d="{{ $linePath }}" fill="none" stroke="url(#salesLine)" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />

                    @foreach ($chartPoints as $index => $point)
                        @php
                            [$pointX, $pointY] = explode(',', $point);
                            $isLast = $index === count($chartPoints) - 1;
                        @endphp
                        @if ($index % 4 === 0 || $isLast)
                            <circle
                                cx="{{ $pointX }}"
                                cy="{{ $pointY }}"
                                r="{{ $isLast ? 6 : 3.5 }}"
                                fill="{{ $isLast ? '#0EA5E9' : '#FFFFFF' }}"
                                stroke="#0EA5E9"
                                stroke-width="2.5"
                            />
                        @endif
                    @endforeach
                </svg>

                <div class="mt-2 flex items-center justify-between text-[10px] font-medium text-muted">
                    <span>{{ $toPersian(12) }} روز پیش</span>
                    <span>{{ $toPersian(9) }} روز پیش</span>
                    <span>{{ $toPersian(6) }} روز پیش</span>
                    <span>{{ $toPersian(3) }} روز پیش</span>
                    <span class="font-bold text-brand-deep">امروز</span>
                </div>
            </div>
        </section>

        <section class="admin-card p-5">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-ink">وضعیت سفارش‌ها</h2>
                    <p class="mt-1 text-[11px] text-muted">{{ $toPersian($ordersCount) }} سفارش ثبت‌شده</p>
                </div>
                <span class="flex size-9 items-center justify-center rounded-xl bg-brand-soft text-brand-deep">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 20V10M10 20V5M16 20v-7M22 20H2" />
                    </svg>
                </span>
            </div>

            <div class="mt-5 space-y-4">
                @foreach (\App\Models\Order::STATUSES as $statusKey => $statusLabel)
                    @php
                        $statusCount = (int) ($statusCounts[$statusKey] ?? 0);
                        $statusPercent = $ordersCount > 0 ? (int) round(($statusCount / $ordersCount) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between gap-2 text-xs">
                            <span class="flex items-center gap-1.5 font-semibold text-ink">
                                <span class="size-2 rounded-full {{ $statusBars[$statusKey] }}"></span>
                                {{ $statusLabel }}
                            </span>
                            <span class="font-extrabold text-ink">{{ $toPersian($statusCount) }}</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-canvas">
                            <div class="h-full rounded-full {{ $statusBars[$statusKey] }}" style="width: {{ $statusPercent }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <a href="#recent-orders" class="mt-5 flex items-center justify-center gap-1.5 rounded-xl border border-line bg-canvas px-3 py-2 text-[11px] font-bold text-brand-deep transition hover:border-brand hover:bg-brand-soft">
                مشاهده همه سفارش‌ها
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
            </a>
        </section>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <section id="recent-orders" class="admin-card overflow-hidden lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-line px-5 py-4">
                <div>
                    <h2 class="text-sm font-extrabold text-ink">سفارش‌های اخیر</h2>
                    <p class="mt-1 text-[11px] text-muted">آخرین سفارش‌های ثبت‌شده در فروشگاه</p>
                </div>
                <span class="badge-soft">{{ $toPersian($pendingCount) }} در انتظار پرداخت</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-sm">
                    <thead class="bg-canvas/70 text-[11px] text-muted">
                        <tr>
                            <th class="px-5 py-3 text-start font-bold">سفارش</th>
                            <th class="px-3 py-3 text-start font-bold">مشتری</th>
                            <th class="px-3 py-3 text-start font-bold">مبلغ</th>
                            <th class="px-3 py-3 text-start font-bold">وضعیت</th>
                            <th class="px-3 py-3 text-start font-bold">تاریخ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($recentOrders as $order)
                            <tr class="transition-colors hover:bg-canvas/70">
                                <td class="whitespace-nowrap px-5 py-3 font-extrabold text-ink">#{{ $toPersian($order->number) }}</td>
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand-soft text-[11px] font-extrabold text-brand-deep">{{ mb_substr($order->customer_name, 0, 1) }}</span>
                                        <div class="leading-tight">
                                            <p class="font-semibold text-ink">{{ $order->customer_name }}</p>
                                            <p class="mt-0.5 text-[10px] text-muted" dir="ltr">{{ $order->phone }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 font-bold text-ink">
                                    {{ $toman($order->total) }}
                                    <span class="text-[10px] font-medium text-muted">تومان</span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="status-pill {{ $statusStyles[$order->status] ?? 'bg-canvas text-ink' }}">{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-[11px] text-muted">{{ $toPersian(\App\Support\Jalali::format($order->placed_at, 'Y/m/d H:i')) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card p-5">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-extrabold text-ink">کالاهای رو به اتمام</h2>
                    <p class="mt-1 text-[11px] text-muted">{{ $toPersian($outOfStock) }} محصول ناموجود در انبار</p>
                </div>
                <span class="flex size-9 items-center justify-center rounded-xl bg-accent-soft text-accent">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M12 8v5M12 17h.01" />
                        <path d="M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                    </svg>
                </span>
            </div>

            <ul class="mt-4 space-y-2.5">
                @forelse ($lowStock as $product)
                    <li class="flex items-center gap-3 rounded-xl border border-line p-2.5 transition-colors hover:bg-canvas/60">
                        <span class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-brand-soft/40">
                            @if ($product->images->first())
                                <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="size-12 object-cover" width="48" height="48" loading="lazy">
                            @endif
                        </span>
                        <div class="min-w-0 flex-1 leading-tight">
                            <p class="truncate text-xs font-bold text-ink">{{ $product->name }}</p>
                            @if ($product->stock === 0)
                                <p class="mt-1 flex items-center gap-1 text-[10px] font-extrabold text-accent">
                                    <span class="size-1.5 rounded-full bg-accent"></span>
                                    ناموجود
                                </p>
                            @else
                                <p class="mt-1 text-[10px] font-bold text-accent">تنها {{ $toPersian($product->stock) }} عدد باقی مانده</p>
                            @endif
                        </div>
                        <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg border border-line bg-white px-2 py-1 text-[10px] font-bold text-brand-deep transition hover:border-brand hover:bg-brand-soft">ویرایش</a>
                    </li>
                @empty
                    <li class="rounded-xl border border-dashed border-line px-3 py-6 text-center text-[11px] text-muted">
                        موجودی همه محصولات در محدوده امن است.
                    </li>
                @endforelse
            </ul>

            <a href="{{ route('admin.products.index') }}" class="btn btn-accent btn-sm mt-4 w-full">مدیریت موجودی انبار</a>
        </section>
    </div>
@endsection
