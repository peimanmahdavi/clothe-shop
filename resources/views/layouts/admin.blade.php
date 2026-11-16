<!DOCTYPE html>
<html lang="fa" dir="rtl" class="scroll-pt-28">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0ea5e9">
        <meta name="robots" content="noindex">

        <title>@yield('title', 'پنل مدیریت | آسمان‌پوش')</title>
        <meta name="description" content="پنل مدیریت فروشگاه آسمان‌پوش">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-canvas">
        @php
            $toPersian = static fn (string|int $value): string => strtr((string) $value, [
                '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
                '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
            ]);

            $adminNav = [
                [
                    'group' => 'مدیریت فروشگاه',
                    'items' => [
                        ['label' => 'داشبورد', 'icon' => 'grid', 'url' => route('admin.dashboard'), 'match' => 'admin.dashboard'],
                        ['label' => 'سفارش‌ها', 'icon' => 'bag', 'url' => route('admin.orders.index'), 'match' => 'admin.orders.*', 'badge' => 'orders'],
                        ['label' => 'تراکنش‌ها', 'icon' => 'receipt', 'url' => route('admin.transactions.index'), 'match' => 'admin.transactions.*'],
                        ['label' => 'محصولات', 'icon' => 'tag', 'url' => route('admin.products.index'), 'match' => 'admin.products.*'],
                        ['label' => 'دسته‌بندی‌ها', 'icon' => 'layers', 'url' => route('admin.categories.index'), 'match' => 'admin.categories.*'],
                        ['label' => 'برندها', 'icon' => 'diamond', 'url' => route('admin.brands.index'), 'match' => 'admin.brands.*'],
                        ['label' => 'تخفیف‌ها و جشنواره', 'icon' => 'percent', 'url' => route('admin.discounts.index'), 'match' => 'admin.discounts.*', 'urgent' => 'کمپین'],
                    ],
                ],
                [
                    'group' => 'سیستم',
                    'items' => [
                        ['label' => 'گزارش‌ها', 'icon' => 'chart', 'url' => '#'],
                        ['label' => 'تنظیمات', 'icon' => 'gear', 'url' => '#'],
                    ],
                ],
            ];
        @endphp

        <div class="fixed inset-0 z-40 hidden bg-ink/40 backdrop-blur-sm lg:hidden" data-admin-overlay aria-hidden="true"></div>

        <aside id="admin-sidebar" class="admin-sidebar" data-admin-sidebar data-open="false" aria-label="ناوبری پنل مدیریت">
            <div class="flex items-center justify-between gap-2 border-b border-line px-5 py-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-linear-to-br from-brand to-brand-strong text-white shadow-lg shadow-brand-strong/25">
                        <svg class="size-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M6.5 16.5A4 4 0 0 1 7 8.6a5.4 5.4 0 0 1 10.3 1 3.7 3.7 0 0 1 .2 6.9" />
                            <path d="M4 16.5h15.5" />
                        </svg>
                    </span>
                    <span class="leading-tight">
                        <span class="block text-base font-extrabold text-ink">آسمان‌پوش</span>
                        <span class="block text-[10px] font-medium text-muted">پنل مدیریت فروشگاه</span>
                    </span>
                </a>

                <button type="button" class="icon-button lg:hidden" data-admin-menu-toggle aria-expanded="false" aria-label="بستن منو">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                </button>
            </div>

            <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-5">
                @foreach ($adminNav as $group)
                    <div>
                        <p class="px-3 pb-2 text-[10px] font-bold text-muted/80">{{ $group['group'] }}</p>
                        <ul class="space-y-1">
                            @foreach ($group['items'] as $item)
                                <li>
                                    @php($isCurrent = isset($item['match']) && request()->routeIs($item['match']))
                                    <a href="{{ $item['url'] }}" class="admin-nav-link" @if ($isCurrent) aria-current="page" @endif>
                                        <span class="admin-nav-icon">
                                            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                @switch($item['icon'])
                                                    @case('grid')
                                                        <rect x="3.5" y="3.5" width="7" height="7" rx="2" />
                                                        <rect x="13.5" y="3.5" width="7" height="7" rx="2" />
                                                        <rect x="3.5" y="13.5" width="7" height="7" rx="2" />
                                                        <rect x="13.5" y="13.5" width="7" height="7" rx="2" />
                                                        @break
                                                    @case('bag')
                                                        <path d="M6 8h12l-1 12H7L6 8Z" />
                                                        <path d="M9 8a3 3 0 0 1 6 0" />
                                                        @break
                                                    @case('tag')
                                                        <path d="M3.5 12.6 12 21l8.5-8.5L12 4H4v8Z" />
                                                        <circle cx="8.2" cy="8.2" r="1.4" />
                                                        @break
                                                    @case('layers')
                                                        <path d="m12 3 8 4.5-8 4.5-8-4.5L12 3Z" />
                                                        <path d="m4 12 8 4.5 8-4.5" />
                                                        <path d="m4 16.5 8 4.5 8-4.5" />
                                                        @break
                                                    @case('users')
                                                        <circle cx="9" cy="8" r="3.2" />
                                                        <path d="M3.5 19.5a5.5 5.5 0 0 1 11 0" />
                                                        <path d="M16 5.4a3.2 3.2 0 0 1 0 6.2M17.5 14.4a5.5 5.5 0 0 1 3 5.1" />
                                                        @break
                                                    @case('receipt')
                                                        <path d="M6 3.5h12a1 1 0 0 1 1 1V20l-2.5-1.5L14 20l-2-1.5L10 20l-2.5-1.5L5 20V4.5a1 1 0 0 1 1-1Z" />
                                                        <path d="M9 8h6M9 11.5h6M9 15h4" />
                                                        @break
                                                    @case('percent')
                                                        <path d="M19 5 5 19" />
                                                        <circle cx="7.5" cy="7.5" r="2.5" />
                                                        <circle cx="16.5" cy="16.5" r="2.5" />
                                                        @break
                                                    @case('diamond')
                                                        <path d="m12 3 3.5 4.5L12 21 8.5 7.5 12 3Z" />
                                                        <path d="M8.5 7.5h7" />
                                                        @break
                                                    @case('chart')
                                                        <path d="M4 20V10M10 20V5M16 20v-7M22 20H2" />
                                                        @break
                                                    @default
                                                        <circle cx="12" cy="12" r="3" />
                                                        <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-2.9 1.2 2 2 0 1 1-4 0 1.7 1.7 0 0 0-2.9-1.2l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.7 1.7 0 0 0 2.1 14a2 2 0 1 1 0-4 1.7 1.7 0 0 0 1.4-2.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1A1.7 1.7 0 0 0 9.2 4a2 2 0 1 1 4 0 1.7 1.7 0 0 0 2.9 1.2l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1A1.7 1.7 0 0 0 21.9 10a2 2 0 1 1 0 4 1.7 1.7 0 0 0-1.4 1Z" />
                                                @endswitch
                                            </svg>
                                        </span>
                                        <span class="flex-1">{{ $item['label'] }}</span>
                                        @isset($item['badge'])
                                            <span class="rounded-full bg-brand-soft px-2 py-0.5 text-[10px] font-extrabold text-brand-deep">{{ $toPersian($pendingOrdersCount) }}</span>
                                        @endisset
                                        @isset($item['urgent'])
                                            <span class="rounded-full bg-accent px-2 py-0.5 text-[10px] font-extrabold text-white shadow-sm shadow-accent/30">{{ $item['urgent'] }}</span>
                                        @endisset
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </nav>

            <div class="space-y-3 border-t border-line p-3">
                <div class="relative overflow-hidden rounded-xl border border-red-100 bg-accent-soft p-3.5">
                    <div class="pointer-events-none absolute -end-6 -top-6 size-16 rounded-full bg-accent/10" aria-hidden="true"></div>
                    <div class="relative flex items-center gap-2">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-accent text-white shadow-sm shadow-accent/30">
                            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5Z" />
                            </svg>
                        </span>
                        <p class="text-xs font-extrabold text-ink">{{ $activeCampaign?->title ?? 'جشنواره‌ای فعال نیست' }}</p>
                    </div>
                    @if ($activeCampaign)
                        <p class="relative mt-2 text-[11px] leading-5 text-muted">
                            {{ $toPersian($activeCampaign->percentage) }}٪ تخفیف تا
                            {{ $activeCampaign->ends_at ? $toPersian($activeCampaign->ends_at->diffInDays(now()) + 1).' روز دیگر' : 'اطلاع نشده' }}
                        </p>
                    @else
                        <p class="relative mt-2 text-[11px] leading-5 text-muted">برای دیده شدن در فروشگاه یک جشنواره فعال بسازید.</p>
                    @endif
                    <a href="{{ route('admin.discounts.index') }}" class="relative mt-2 inline-flex items-center gap-1 text-[11px] font-bold text-accent transition hover:text-accent-deep">
                        مدیریت جشنواره
                        <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                    </a>
                </div>

                <div class="flex items-center gap-2.5 rounded-xl border border-line bg-canvas px-3 py-2.5">
                    <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-brand-soft text-sm font-extrabold text-brand-deep">ز</span>
                    <div class="min-w-0 flex-1 leading-tight">
                        <p class="truncate text-xs font-bold text-ink">زهرا محمدی</p>
                        <p class="text-[10px] text-muted">مدیر فروشگاه</p>
                    </div>
                    <button type="button" class="icon-button size-8" aria-label="خروج از حساب">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M15 4h3.5A1.5 1.5 0 0 1 20 5.5v13a1.5 1.5 0 0 1-1.5 1.5H15" />
                            <path d="M10 8 6 12l4 4" />
                            <path d="M6 12h9" />
                        </svg>
                    </button>
                </div>
            </div>
        </aside>

        <div class="lg:ps-72">
            <header class="sticky top-0 z-30 border-b border-line bg-white/90 backdrop-blur-md">
                <div class="flex items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    <button type="button" class="icon-button lg:hidden" data-admin-menu-toggle aria-expanded="false" aria-controls="admin-sidebar" aria-label="باز کردن منو">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <path d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </button>

                    <div class="min-w-0">
                        <p class="flex items-center gap-1 text-[11px] font-medium text-muted">
                            پنل مدیریت
                            <span class="text-line">/</span>
                            @yield('breadcrumb', 'داشبورد')
                        </p>
                        <p class="truncate text-sm font-extrabold text-ink">@yield('page-title', 'داشبورد')</p>
                    </div>

                    <div class="ms-auto flex items-center gap-2">
                        <div class="relative hidden md:block">
                            <svg class="pointer-events-none absolute inset-y-0 start-3.5 my-auto size-4.5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3.5-3.5" />
                            </svg>
                            <input type="search" class="search-input w-52 lg:w-64" placeholder="جست‌وجو در سفارش‌ها، محصولات…" aria-label="جست‌وجو در پنل مدیریت">
                        </div>

                        <button type="button" class="icon-button md:hidden" data-admin-search-toggle aria-expanded="false" aria-label="جست‌وجو">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3.5-3.5" />
                            </svg>
                        </button>

                        <button type="button" class="icon-button" aria-label="اعلان‌ها">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M18 8a6 6 0 1 0-12 0c0 5-2 6-2 6h16s-2-1-2-6" />
                                <path d="M10.3 20a2 2 0 0 0 3.4 0" />
                            </svg>
                            <span class="absolute -top-1 -end-1 flex size-4.5 items-center justify-center rounded-full bg-accent text-[9px] font-extrabold text-white shadow-sm">۳</span>
                        </button>

                        <a href="{{ route('home') }}" class="btn btn-outline btn-sm hidden sm:inline-flex">
                            مشاهده فروشگاه
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                        </a>

                        <button type="button" class="flex size-9 items-center justify-center rounded-full bg-brand-soft text-xs font-extrabold text-brand-deep ring-2 ring-white" aria-label="حساب کاربری">ز</button>
                    </div>
                </div>
            </header>

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                @if (session('status'))
                    <div class="mb-4 flex items-center gap-2 rounded-xl border border-brand/30 bg-brand-soft px-4 py-3 text-xs font-bold text-brand-deep">
                        <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m5 13 4 4L19 7" />
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-xs font-bold text-accent">
                        {{ $errors->first() }}
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="px-4 pb-8 text-center text-[11px] text-muted sm:px-6 lg:px-8">
                © ۱۴۰۵ آسمان‌پوش — پنل مدیریت
            </footer>
        </div>
    </body>
</html>
