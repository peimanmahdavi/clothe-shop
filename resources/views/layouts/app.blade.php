<!DOCTYPE html>
<html lang="fa" dir="rtl" class="scroll-pt-36">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0ea5e9">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'آسمان‌پوش | فروشگاه پوشاک مینیمال')</title>
        <meta name="description" content="@yield('description', 'آسمان‌پوش؛ فروشگاه پوشاک مینیمال با پارچه‌های نخی، رنگ‌های آرام و ارسال سریع به سراسر ایران.')">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-canvas">
        <a href="#main" class="sr-only rounded-xl bg-ink px-4 py-2 text-sm font-bold text-white focus:not-sr-only focus:absolute focus:top-3 focus:start-3 focus:z-[60]">
            پرش به محتوای اصلی
        </a>

        @php
            $navItems = [
                ['label' => 'خانه', 'icon' => 'home', 'href' => route('home'), 'current' => request()->routeIs('home')],
                ['label' => 'محصولات', 'icon' => 'tag', 'href' => route('products.index'), 'current' => request()->routeIs('products.*')],
                ['label' => 'پرفروش‌ها', 'icon' => 'fire', 'href' => route('home') . '#products'],
                ['label' => 'دسته‌بندی‌ها', 'icon' => 'layers', 'href' => route('home') . '#categories'],
                ['label' => 'حراج ویژه', 'icon' => 'percent', 'href' => route('home') . '#sale', 'urgent' => true],
                ['label' => 'تماس با ما', 'icon' => 'phone', 'href' => route('home') . '#contact'],
            ];
        @endphp

        <header class="sticky top-0 z-50 w-full">
            <div class="border-b border-red-100 bg-accent-soft">
                <div class="container-page flex items-center justify-between gap-3 py-2">
                    <p class="flex min-w-0 items-center gap-2 text-xs font-semibold text-accent sm:text-sm">
                        <span class="relative flex size-2 shrink-0 rounded-full bg-accent">
                            <span class="absolute inline-flex size-full animate-ping rounded-full bg-accent/70"></span>
                        </span>
                        <span class="truncate">جشنواره پایان فصل؛ تا ۵۰٪ تخفیف روی کالکشن پاییز</span>
                    </p>
                    <a href="{{ route('home') }}#sale" class="hidden shrink-0 items-center gap-1 text-xs font-bold text-accent underline decoration-accent/40 underline-offset-4 transition hover:decoration-accent sm:inline-flex">
                        مشاهده پیشنهادها
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="border-b border-line bg-white/90 backdrop-blur-md transition-shadow duration-300" data-site-header>
                <div class="container-page flex items-center justify-between gap-4 py-3.5">
                    <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3">
                        <span class="flex size-11 items-center justify-center rounded-2xl bg-linear-to-br from-brand to-brand-strong text-white shadow-lg shadow-brand-strong/30">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6.5 16.5A4 4 0 0 1 7 8.6a5.4 5.4 0 0 1 10.3 1 3.7 3.7 0 0 1 .2 6.9" />
                                <path d="M4 16.5h15.5" />
                                <path d="M15.5 5.2v-1" />
                            </svg>
                        </span>
                        <span class="leading-tight">
                            <span class="block text-lg font-extrabold text-ink">آسمان‌پوش</span>
                            <span class="block text-[11px] font-medium text-muted">پوشاک مینیمال ایرانی</span>
                        </span>
                    </a>

                    <nav class="hidden items-center gap-7 lg:flex" aria-label="ناوبری اصلی">
                        @foreach ($navItems as $item)
                            <a href="{{ $item['href'] }}" @class(['nav-link', 'text-accent hover:text-accent-deep' => $item['urgent'] ?? false]) @if ($item['current'] ?? false) aria-current="page" @endif>
                                {{ $item['label'] }}
                            </a>
                        @endforeach
                    </nav>

                    <div class="flex items-center gap-2">
                        <div class="relative hidden md:block">
                            <svg class="pointer-events-none absolute inset-y-0 start-3.5 my-auto size-4.5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3.5-3.5" />
                            </svg>
                            <input type="search" class="search-input w-56 lg:w-64" placeholder="جست‌وجوی محصول، رنگ یا سایز" aria-label="جست‌وجوی محصول">
                        </div>

                        @auth
                            <a href="{{ route('profile.dashboard') }}" class="icon-button hidden sm:flex" aria-label="حساب کاربری" title="حساب کاربری">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 21a8 8 0 0 1 16 0" />
                                </svg>
                            </a>
                            <form method="POST" action="{{ route('auth.logout') }}" class="hidden sm:flex">
                                @csrf
                                <button type="submit" class="icon-button" aria-label="خروج" title="خروج">
                                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <path d="M16 17l5-5-5-5M21 12H9" />
                                    </svg>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('auth.login') }}" class="hidden items-center gap-1.5 rounded-xl border border-line bg-white px-3 py-2 text-xs font-bold text-ink transition hover:border-brand hover:text-brand-deep sm:inline-flex">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 21a8 8 0 0 1 16 0" />
                                </svg>
                                ورود / ثبت‌نام
                            </a>
                        @endauth

                        <a href="#products" class="icon-button hidden sm:flex" aria-label="علاقه‌مندی‌ها">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20.8 4.9a5.2 5.2 0 0 0-7.4 0L12 6.3l-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4L12 21l8.8-8.7a5.2 5.2 0 0 0 0-7.4Z" />
                            </svg>
                        </a>

                        @auth
                            <a href="{{ route('profile.dashboard') }}" class="icon-button sm:hidden" aria-label="پنل کاربری" title="پنل کاربری">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="8" r="4" />
                                    <path d="M4 21a8 8 0 0 1 16 0" />
                                </svg>
                            </a>
                        @endauth

                        <a href="{{ route('cart.index') }}" class="icon-button" aria-label="سبد خرید" title="سبد خرید">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 8h12l-1 12H7L6 8Z" />
                                <path d="M9 8a3 3 0 0 1 6 0" />
                            </svg>
                            <span class="absolute -top-1.5 -end-1.5 flex size-5 items-center justify-center rounded-full bg-accent text-[10px] font-extrabold text-white shadow-sm @if (($cartCount ?? 0) === 0) hidden @endif" data-cart-count data-count="{{ $cartCount ?? 0 }}">{{ strtr((string) ($cartCount ?? 0), ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹']) }}</span>
                        </a>

                        <button type="button" class="icon-button lg:hidden" data-menu-toggle aria-expanded="false" aria-controls="mobile-menu" aria-label="منوی اصلی">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="M4 7h16M4 12h16M4 17h16" />
                            </svg>
                        </button>
                    </div>
                </div>

            </div>

            {{-- Mobile slide-over drawer --}}
            <div id="mobile-menu" class="fixed inset-0 z-50 hidden lg:hidden" data-menu-panel aria-hidden="true">
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-slate-900/40 backdrop-blur-xs" data-menu-backdrop></div>

                {{-- Panel (slides from the right in RTL) --}}
                <aside class="absolute inset-y-0 end-0 flex h-full w-80 max-w-[85vw] flex-col bg-white shadow-2xl transition-transform duration-300 ease-out"
                    role="dialog" aria-modal="true" aria-label="منوی اصلی" data-menu-drawer>

                    {{-- Header: logo right, close left --}}
                    <div class="flex items-center justify-between gap-2 border-b border-line px-4 py-3.5">
                        <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                            <span class="flex size-9 items-center justify-center rounded-xl bg-linear-to-br from-brand to-brand-strong text-white shadow-md shadow-brand-strong/30">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6.5 16.5A4 4 0 0 1 7 8.6a5.4 5.4 0 0 1 10.3 1 3.7 3.7 0 0 1 .2 6.9" />
                                    <path d="M4 16.5h15.5" />
                                </svg>
                            </span>
                            <span class="text-base font-extrabold text-ink">آسمان‌پوش</span>
                        </a>
                        <button type="button" class="icon-button" data-menu-close aria-label="بستن منو">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <path d="M6 6l12 12M18 6 6 18" />
                            </svg>
                        </button>
                    </div>

                    {{-- Scrollable body --}}
                    <div class="flex-1 overflow-y-auto px-4 py-4">
                        {{-- Search --}}
                        <div class="relative mb-4">
                            <svg class="pointer-events-none absolute inset-y-0 start-3.5 my-auto size-4.5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                                <circle cx="11" cy="11" r="7" />
                                <path d="m20 20-3.5-3.5" />
                            </svg>
                            <input type="search" class="search-input" placeholder="جست‌وجوی محصول، رنگ یا سایز" aria-label="جست‌وجوی محصول">
                        </div>

                        {{-- Account card --}}
                        @auth
                            <div class="mb-4 rounded-2xl border border-brand/20 bg-brand-soft/50 p-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand-strong text-base font-extrabold text-white shadow-md shadow-brand-strong/30">
                                        {{ mb_substr(auth()->user()->getFullNameAttribute(), 0, 1) }}
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-extrabold text-ink">{{ auth()->user()->getFullNameAttribute() }}</p>
                                        <p class="truncate text-xs text-muted" dir="ltr">{{ auth()->user()->phone }}</p>
                                    </div>
                                </div>

                                <a href="{{ route('profile.dashboard') }}" class="btn btn-primary btn-sm mt-3 w-full">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="3" width="7" height="7" rx="1.5" /><rect x="3" y="14" width="7" height="7" rx="1.5" /><rect x="14" y="14" width="7" height="7" rx="1.5" /></svg>
                                    پنل کاربری و سفارش‌ها
                                </a>

                                <div class="mt-2 grid grid-cols-2 gap-2">
                                    <a href="{{ route('profile.orders') }}" class="flex items-center justify-center gap-1.5 rounded-xl border border-line bg-white px-2 py-2 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8h12l-1 12H7L6 8Z" /><path d="M9 8a3 3 0 0 1 6 0" /></svg>
                                        سفارش‌های من
                                    </a>
                                    <a href="{{ route('products.index') }}" class="flex items-center justify-center gap-1.5 rounded-xl border border-line bg-white px-2 py-2 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">
                                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.9a5.2 5.2 0 0 0-7.4 0L12 6.3l-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4L12 21l8.8-8.7a5.2 5.2 0 0 0 0-7.4Z" /></svg>
                                        علاقه‌مندی‌ها
                                    </a>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('auth.login') }}" class="btn btn-primary mb-4 w-full">
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4" /><path d="M4 21a8 8 0 0 1 16 0" /></svg>
                                ورود / ثبت‌نام
                            </a>
                        @endauth

                        {{-- Nav links --}}
                        <nav class="space-y-1" aria-label="ناوبری موبایل">
                            @foreach ($navItems as $item)
                                <a href="{{ $item['href'] }}" @class([
                                    'flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-semibold transition',
                                    'bg-brand-soft text-brand-deep' => $item['current'] ?? false,
                                    'text-accent hover:bg-accent-soft' => $item['urgent'] ?? false,
                                    'text-ink hover:bg-canvas hover:text-brand-deep' => ! ($item['current'] ?? false) && ! ($item['urgent'] ?? false),
                                ]) @if ($item['current'] ?? false) aria-current="page" @endif>
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-lg @if ($item['current'] ?? false) bg-white text-brand-deep @else bg-canvas text-muted @endif">
                                        <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            @switch($item['icon'])
                                                @case('home')
                                                    <path d="m3 11 9-8 9 8" /><path d="M5 10v10h14V10" />
                                                    @break
                                                @case('tag')
                                                    <path d="M3.5 12.6 12 21l8.5-8.5L12 4H4v8Z" /><circle cx="8.2" cy="8.2" r="1.4" />
                                                    @break
                                                @case('fire')
                                                    <path d="M12 3c1 3-1.5 4.5-1.5 7A2.5 2.5 0 0 0 13 12c.5-1.5.5-2.5.5-2.5 1.5 1 3 3 3 5.5a4.5 4.5 0 0 1-9 0c0-4 2-6.5 4.5-12Z" />
                                                    @break
                                                @case('layers')
                                                    <path d="m12 3 8 4.5-8 4.5-8-4.5L12 3Z" /><path d="m4 12 8 4.5 8-4.5" /><path d="m4 16.5 8 4.5 8-4.5" />
                                                    @break
                                                @case('percent')
                                                    <path d="M19 5 5 19" /><circle cx="7" cy="7" r="2.4" /><circle cx="17" cy="17" r="2.4" />
                                                    @break
                                                @case('phone')
                                                    <path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2Z" />
                                                    @break
                                            @endswitch
                                        </svg>
                                    </span>
                                    <span class="flex-1">{{ $item['label'] }}</span>
                                    @if ($item['urgent'] ?? false)
                                        <span class="badge-sale text-[10px]">تخفیف</span>
                                    @endif
                                    <svg class="size-4 shrink-0 text-muted/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 6 9 12l6 6" /></svg>
                                </a>
                            @endforeach
                        </nav>
                    </div>

                    {{-- Sticky footer: logout --}}
                    @auth
                        <div class="border-t border-line p-4">
                            <form method="POST" action="{{ route('auth.logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-bold text-red-600 transition hover:bg-red-100">
                                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><path d="M16 17l5-5-5-5M21 12H9" /></svg>
                                    خروج از حساب
                                </button>
                            </form>
                        </div>
                    @endauth
                </aside>
            </div>
        </header>

        <main id="main">
            @yield('content')
        </main>

        <footer id="contact" class="mt-20 bg-ink text-slate-300">
            <div class="container-page grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <span class="flex size-11 items-center justify-center rounded-2xl bg-linear-to-br from-brand to-brand-strong text-white shadow-lg shadow-brand/20">
                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6.5 16.5A4 4 0 0 1 7 8.6a5.4 5.4 0 0 1 10.3 1 3.7 3.7 0 0 1 .2 6.9" />
                                <path d="M4 16.5h15.5" />
                            </svg>
                        </span>
                        <span class="text-lg font-extrabold text-white">آسمان‌پوش</span>
                    </a>
                    <p class="text-sm leading-7 text-slate-400">
                        پوشاک مینیمال با پارچه‌های نخی و رنگ‌های آرام؛ برای روزهایی که می‌خواهید راحت و شیک باشید.
                    </p>
                    <div class="flex items-center gap-2">
                        <a href="#" class="flex size-9 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-brand-strong" aria-label="اینستاگرام">
                            <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true">
                                <rect x="3" y="3" width="18" height="18" rx="5" />
                                <circle cx="12" cy="12" r="4" />
                                <path d="M17.5 6.5h.01" />
                            </svg>
                        </a>
                        <a href="#" class="flex size-9 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-brand-strong" aria-label="تلگرام">
                            <svg class="size-4.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M21.5 4.3 2.9 11.5c-.8.3-.8 1.4 0 1.7l4.4 1.5 1.7 5.2c.2.7 1.1.8 1.6.3l2.4-2.4 4.4 3.2c.6.4 1.4.1 1.6-.6l3-14.9c.2-.9-.7-1.6-1.5-1.2Zm-2.9 3.4-7.9 6.9c-.2.2-.3.4-.3.7l-.2 2.4-1.2-3.6 9.1-6.6c.3-.2.7.1.5.2Z" />
                            </svg>
                        </a>
                        <a href="#" class="flex size-9 items-center justify-center rounded-xl bg-white/10 text-white transition hover:bg-brand-strong" aria-label="واتساپ">
                            <svg class="size-4.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.4A10 10 0 1 0 12 2Zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .1-1.7-.1a12 12 0 0 1-5.9-5.2c-.4-.7-.7-1.5-.6-2.2 0-.6.5-1.4 1-1.6.3-.1.6-.1.8 0l.9 1.6c.1.3 0 .5-.1.7l-.4.5c-.1.2-.2.3 0 .6.5.9 1.5 1.9 2.5 2.4.3.2.4.1.6-.1l.5-.6c.2-.2.4-.2.6-.1l1.6.8c.3.1.4.4.3.7Z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h2 class="mb-4 text-sm font-extrabold text-white">فروشگاه</h2>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="{{ route('products.index') }}" class="transition hover:text-brand">همه محصولات</a></li>
                        <li><a href="{{ route('home') }}#categories" class="transition hover:text-brand">دسته‌بندی‌ها</a></li>
                        <li><a href="{{ route('home') }}#sale" class="font-bold text-accent transition hover:text-accent-deep">حراج پایان فصل</a></li>
                        <li><a href="{{ route('products.index', ['sort' => 'newest']) }}" class="transition hover:text-brand">جدیدترین‌ها</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="mb-4 text-sm font-extrabold text-white">خدمات مشتریان</h2>
                    <ul class="space-y-3 text-sm text-slate-400">
                        <li><a href="#" class="transition hover:text-brand">پیگیری سفارش</a></li>
                        <li><a href="#" class="transition hover:text-brand">راهنمای سایز</a></li>
                        <li><a href="#" class="transition hover:text-brand">شرایط بازگشت کالا</a></li>
                        <li><a href="#" class="transition hover:text-brand">سؤالات متداول</a></li>
                    </ul>
                </div>

                <div class="space-y-4">
                    <h2 class="text-sm font-extrabold text-white">تماس با ما</h2>
                    <p class="text-sm leading-7 text-slate-400">
                        تهران، خیابان ولیعصر، برج آسمان، طبقه سوم<br>
                        شنبه تا پنجشنبه، ۹ صبح تا ۱۸
                    </p>
                    <div class="space-y-2 text-sm">
                        <a href="tel:+982191002233" class="flex items-center gap-2 text-slate-300 transition hover:text-brand" dir="ltr">
                            <span dir="rtl">۰۲۱-۹۱۰۰۲۲۳۳</span>
                        </a>
                        <a href="mailto:hello@asemanpoosh.ir" class="flex items-center gap-2 text-slate-300 transition hover:text-brand" dir="ltr">hello@asemanpoosh.ir</a>
                    </div>
                    <div class="flex flex-wrap gap-2 pt-1 text-[11px] font-semibold text-slate-300">
                        <span class="rounded-lg border border-white/15 px-2.5 py-1.5">پرداخت امن</span>
                        <span class="rounded-lg border border-white/15 px-2.5 py-1.5">ضمانت اصالت</span>
                        <span class="rounded-lg border border-white/15 px-2.5 py-1.5">ارسال سراسری</span>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10">
                <div class="container-page flex flex-col items-center justify-between gap-3 py-5 text-xs text-slate-400 sm:flex-row">
                    <p>© ۱۴۰۵ آسمان‌پوش — کلیه حقوق محفوظ است.</p>
                    <p class="flex items-center gap-2">
                        <span class="size-1.5 rounded-full bg-brand"></span>
                        طراحی و اجرا با هویت رنگی آسمان و سفید
                    </p>
                </div>
            </div>
        </footer>

        <div class="pointer-events-none fixed bottom-6 start-6 z-[60] translate-y-6 opacity-0 transition-all duration-300" data-toast role="status" aria-live="polite">
            <div class="flex items-center gap-3 rounded-2xl border border-brand/30 bg-white px-4 py-3 shadow-xl shadow-sky-950/10">
                <span class="flex size-9 items-center justify-center rounded-full bg-brand-soft text-brand-deep">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m5 13 4 4L19 7" />
                    </svg>
                </span>
                <p class="text-sm font-bold text-ink" data-toast-message>محصول به سبد خرید اضافه شد.</p>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
