@extends('layouts.app')

@section('title', 'آسمان‌پوش | کالکشن پاییز با تا ۴۵٪ تخفیف')

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

        $categoryIcons = ['tshirt', 'dress', 'sneakers', 'hoodie', 'pants'];

        $heroImage = $heroProduct?->images->get($heroProduct->defaultImageIndex())
            ?? $heroProduct?->images->first();

        $campaignPercentage = $activeDiscount?->percentage ?? 45;
    @endphp

    {{-- Hero --}}
    <section id="home" class="relative overflow-hidden bg-linear-to-b from-brand-soft/80 via-canvas to-canvas">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <div class="absolute -end-32 -top-28 size-[26rem] rounded-full bg-brand/25 blur-3xl"></div>
            <div class="absolute top-40 -start-40 size-80 rounded-full bg-brand-strong/10 blur-3xl"></div>
            <div class="absolute inset-x-0 bottom-0 h-40 bg-linear-to-t from-canvas to-transparent"></div>
            <div class="absolute inset-0 opacity-40 [background-image:radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_top,black,transparent_65%)]"></div>
        </div>

        <div class="container-page relative grid items-center gap-12 py-14 lg:grid-cols-2 lg:gap-10 lg:py-20">
            <div class="max-w-xl">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="eyebrow shadow-sm">
                        <span class="size-1.5 rounded-full bg-brand-strong"></span>
                        کالکشن پاییز ۱۴۰۵
                    </span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-accent-soft px-3 py-1.5 text-xs font-bold text-accent">
                        <span class="size-1.5 rounded-full bg-accent"></span>
                        تازه رسید
                    </span>
                </div>

                <h1 class="mt-6 text-[2.1rem] leading-[1.35] font-extrabold text-ink sm:text-5xl sm:leading-[1.3] lg:text-[3.35rem]">
                    استایلت را با
                    <span class="relative inline-block text-brand-deep">
                        آبی آسمان
                        <svg class="absolute -bottom-2 start-0 h-3 w-full text-brand" viewBox="0 0 200 14" fill="none" preserveAspectRatio="none" aria-hidden="true">
                            <path d="M3 10c44-6 96-7 194-3" stroke="currentColor" stroke-width="6" stroke-linecap="round" />
                        </svg>
                    </span>
                    تازه کن
                </h1>

                <p class="mt-6 max-w-md text-sm leading-7 text-muted sm:text-base sm:leading-8">
                    پارچه‌های نخی و رنگ‌های آرام، با برش‌های مینیمال و راحتی تمام‌روز. کالکشن جدید آسمان‌پوش با
                    <span class="font-bold text-ink">تا {{ $toPersian($campaignPercentage) }}٪ تخفیف</span>
                    برای اولین بار عرضه شده است.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('products.index') }}" class="btn btn-primary">
                        خرید کالکشن جدید
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <a href="#sale" class="btn btn-outline">
                        پیشنهاد ویژه امروز
                        <span class="rounded-full bg-accent px-2 py-0.5 text-[10px] font-extrabold text-white">٪{{ $toPersian($campaignPercentage) }}</span>
                    </a>
                </div>

                <ul class="mt-8 flex flex-wrap items-center gap-x-6 gap-y-3 text-xs font-semibold text-muted">
                    <li class="flex items-center gap-2">
                        <svg class="size-4 text-brand-strong" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M3 7h11v9H3z" /><path d="M14 10h4l3 3v3h-7z" /><circle cx="7" cy="18" r="1.8" /><circle cx="17.5" cy="18" r="1.8" />
                        </svg>
                        ارسال رایگان بالای ۵۰۰ هزار تومان
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="size-4 text-brand-strong" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 3 5 6v6c0 4.4 3 7.6 7 9 4-1.4 7-4.6 7-9V6l-7-3Z" /><path d="m9 12 2 2 4-4" />
                        </svg>
                        ضمانت اصالت کالا
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="size-4 text-brand-strong" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 12a8 8 0 1 1-2.3-5.6" /><path d="M20 4v4h-4" />
                        </svg>
                        ۷ روز بازگشت بدون شرط
                    </li>
                </ul>

                <div class="mt-9 flex items-center gap-6 border-t border-line/80 pt-6">
                    <div>
                        <p class="text-xl font-extrabold text-ink">+۱۲ هزار</p>
                        <p class="mt-0.5 text-[11px] font-medium text-muted">مشتری راضی</p>
                    </div>
                    <div class="h-9 w-px bg-line"></div>
                    <div>
                        <p class="flex items-center gap-1 text-xl font-extrabold text-ink">
                            ۴.۹
                            <svg class="size-4 text-amber-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3Z" />
                            </svg>
                        </p>
                        <p class="mt-0.5 text-[11px] font-medium text-muted">امتیاز از ۵</p>
                    </div>
                    <div class="h-9 w-px bg-line"></div>
                    <div>
                        <p class="text-xl font-extrabold text-ink">۲۴ ساعت</p>
                        <p class="mt-0.5 text-[11px] font-medium text-muted">ارسال در تهران</p>
                    </div>
                </div>
            </div>

            <div class="relative mx-auto w-full max-w-md lg:max-w-lg">
                <div class="absolute inset-8 rounded-full bg-brand-strong/25 blur-3xl" aria-hidden="true"></div>

                <div class="relative overflow-hidden rounded-[2.5rem] border border-white/70 bg-linear-to-br from-white via-brand-soft/70 to-brand/30 p-6 shadow-[0_50px_90px_-50px_rgba(2,132,199,0.7)] sm:p-8">
                    <div class="pointer-events-none absolute -end-12 -top-12 size-44 rounded-full border-[20px] border-white/50" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -start-10 -bottom-14 size-36 rounded-full bg-brand/25 blur-2xl" aria-hidden="true"></div>

                    <span class="relative inline-flex items-center gap-1.5 rounded-full bg-white/85 px-3 py-1.5 text-[11px] font-bold text-brand-deep shadow-sm backdrop-blur">
                        <span class="size-1.5 rounded-full bg-brand-strong"></span>
                        انتخاب ویژه استایلیست
                    </span>

                    @if ($heroImage)
                        <div class="relative mx-auto mt-3 aspect-square w-full max-w-xs overflow-hidden rounded-[1.75rem] border border-white/70 bg-white shadow-lg shadow-sky-950/10">
                            <img src="{{ $heroImage->url() }}" alt="{{ $heroProduct->name }}" class="h-full w-full animate-float object-cover" width="420" height="420" fetchpriority="high">
                        </div>
                    @else
                        <div class="relative mx-auto mt-3 flex aspect-square w-full max-w-xs items-center justify-center rounded-[1.75rem] border border-white/70 bg-white/70 text-brand">
                            <svg class="size-24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                                <path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" />
                            </svg>
                        </div>
                    @endif

                    @if ($heroProduct)
                        <div class="relative mt-4 flex items-center justify-between gap-3 rounded-2xl border border-white/70 bg-white/70 px-4 py-3 backdrop-blur">
                            <div>
                                <p class="text-xs font-bold text-ink">{{ $heroProduct->name }}</p>
                                <p class="mt-0.5 text-[11px] text-muted">{{ $heroProduct->brand?->name ?? $heroProduct->category?->name }}</p>
                            </div>
                            <div class="shrink-0 text-end">
                                <p class="text-sm font-extrabold text-ink">{{ $toman($heroProduct->finalPrice()) }}</p>
                                @if ($heroProduct->discountPercent() > 0)
                                    <p class="text-[10px] font-medium text-muted line-through">{{ $toman($heroProduct->price) }}</p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                @if ($heroProduct && $heroProduct->discountPercent() > 0)
                    <div class="absolute -top-3 end-2 flex flex-col items-center rounded-2xl bg-accent px-4 py-3 text-white shadow-xl shadow-accent/40 sm:end-4" aria-hidden="true">
                        <span class="text-2xl font-extrabold leading-none">٪{{ $toPersian($heroProduct->discountPercent()) }}</span>
                        <span class="mt-1 text-[10px] font-bold opacity-90">تخفیف</span>
                    </div>
                @endif

                <div class="absolute -bottom-5 start-0 hidden w-52 rounded-2xl border border-line bg-white p-3 shadow-xl shadow-sky-950/10 sm:block">
                    <div class="flex items-center gap-2">
                        <span class="flex size-8 items-center justify-center rounded-xl bg-brand-soft text-brand-deep">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="m5 13 4 4L19 7" />
                            </svg>
                        </span>
                        <p class="text-[11px] font-bold text-ink">موجود در انبار</p>
                    </div>
                    <div class="mt-2 flex items-center justify-between text-[11px]">
                        <span class="text-muted">ارسال از ۲۴ ساعت</span>
                        <span class="font-bold text-brand-deep">تهران</span>
                    </div>
                </div>

                <div class="absolute top-1/3 -start-3 hidden items-center gap-2 rounded-2xl border border-line bg-white px-3 py-2 shadow-lg shadow-sky-950/5 lg:flex">
                    <span class="flex size-7 items-center justify-center rounded-lg bg-accent-soft text-accent text-xs font-extrabold">٪</span>
                    <p class="text-[11px] font-bold text-ink">کد تخفیف {{ $activeDiscount?->code ?? 'SKY25' }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Service highlights --}}
    <section class="relative z-10 -mt-4">
        <div class="container-page">
            <div class="grid gap-3 rounded-3xl border border-line bg-white p-3 shadow-card sm:grid-cols-2 sm:p-4 lg:grid-cols-4">
                @foreach ([
                    ['title' => 'ارسال سریع و رایگان', 'text' => 'برای خریدهای بالای ۵۰۰ هزار تومان', 'icon' => 'truck'],
                    ['title' => '۷ روز بازگشت کالا', 'text' => 'بدون شرط و بدون دردسر', 'icon' => 'return'],
                    ['title' => 'پرداخت امن', 'text' => 'درگاه بانکی معتبر', 'icon' => 'shield'],
                    ['title' => 'پشتیبانی واقعی', 'text' => 'هر روز از ۹ صبح تا ۱۸', 'icon' => 'support'],
                ] as $service)
                    <div class="group flex items-center gap-3 rounded-2xl px-3 py-3 transition-colors duration-300 hover:bg-canvas">
                        <span class="icon-tile group-hover:bg-brand group-hover:text-white">
                            @if ($service['icon'] === 'truck')
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 7h11v9H3z" /><path d="M14 10h4l3 3v3h-7z" /><circle cx="7" cy="18" r="1.8" /><circle cx="17.5" cy="18" r="1.8" />
                                </svg>
                            @elseif ($service['icon'] === 'return')
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 12a8 8 0 1 1-2.3-5.6" /><path d="M20 4v4h-4" />
                                </svg>
                            @elseif ($service['icon'] === 'shield')
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 3 5 6v6c0 4.4 3 7.6 7 9 4-1.4 7-4.6 7-9V6l-7-3Z" /><path d="m9 12 2 2 4-4" />
                                </svg>
                            @else
                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M4 13a8 8 0 1 1 16 0v4a3 3 0 0 1-3 3h-2" /><rect x="2.5" y="12" width="3.5" height="6" rx="1.5" /><rect x="18" y="12" width="3.5" height="6" rx="1.5" />
                                </svg>
                            @endif
                        </span>
                        <div>
                            <p class="text-sm font-bold text-ink">{{ $service['title'] }}</p>
                            <p class="mt-0.5 text-[11px] text-muted">{{ $service['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Limited time sale with countdown --}}
    <section id="sale" class="pt-14 sm:pt-16">
        <div class="container-page">
            <div class="relative w-full max-w-full overflow-hidden rounded-[2rem] border border-red-100 bg-accent-soft p-4 sm:p-8 lg:p-10">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute -start-16 -top-16 size-56 rounded-full border-[24px] border-white/60"></div>
                    <div class="absolute -bottom-20 end-1/4 size-48 rounded-full bg-accent/10 blur-2xl"></div>
                    <div class="absolute inset-y-0 end-0 w-1/2 opacity-[0.35] [background-image:radial-gradient(#ef4444_1px,transparent_1px)] [background-size:22px_22px] [mask-image:linear-gradient(to_left,black,transparent)]"></div>
                </div>

                <div class="relative grid gap-8 lg:grid-cols-2 lg:items-center">
                    <div>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-accent px-3 py-1.5 text-xs font-extrabold text-white shadow-lg shadow-accent/30">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
                            </svg>
                            فرصت محدود
                        </span>

                        <h2 class="mt-4 text-2xl font-extrabold leading-[1.5] text-ink sm:text-4xl sm:leading-[1.4]">
                            {{ $activeDiscount?->title ?? 'حراج پایان فصل' }}
                            <span class="text-accent">تا {{ $toPersian($campaignPercentage) }}٪ تخفیف</span>
                        </h2>

                        <p class="mt-3 w-full max-w-full break-words whitespace-normal text-sm leading-7 text-muted sm:max-w-md">
                            {{ $activeDiscount?->description ?? 'روی منتخبی از محصولات کالکشن پاییز. قیمت‌ها پس از پایان جشنواره به حالت قبل برمی‌گردند؛ تا موجودی هست، بخرید.' }}
                        </p>

                        <div class="mt-6 flex w-full flex-wrap items-center gap-3">
                            <a href="#products" class="btn btn-accent">
                                مشاهده محصولات حراج
                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M19 12H5M12 19l-7-7 7-7" />
                                </svg>
                            </a>
                            <span class="badge-stock bg-white/80">
                                <span class="size-1.5 animate-pulse rounded-full bg-accent"></span>
                                موجودی محدود
                            </span>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-red-100 bg-white/70 p-5 backdrop-blur sm:p-6">
                        <p class="text-center text-sm font-bold text-ink">
                            پایان جشنواره تا
                            <span class="text-accent">{{ $activeDiscount?->ends_at ? 'پایان زمان اعلام‌شده' : 'آخرین لحظه ماه' }}</span>
                        </p>

                        <div dir="ltr" class="mt-4 grid w-full max-w-full grid-cols-[1fr_auto_1fr_auto_1fr_auto_1fr] items-center gap-1 sm:gap-2.5" data-countdown data-deadline="{{ $activeDiscount?->ends_at?->toIso8601String() }}">
                            <div class="countdown-cell">
                                <span class="countdown-value" data-unit="days">۰۰</span>
                                <span class="countdown-label">روز</span>
                            </div>
                            <span class="countdown-separator" aria-hidden="true">:</span>
                            <div class="countdown-cell">
                                <span class="countdown-value" data-unit="hours">۰۰</span>
                                <span class="countdown-label">ساعت</span>
                            </div>
                            <span class="countdown-separator" aria-hidden="true">:</span>
                            <div class="countdown-cell">
                                <span class="countdown-value" data-unit="minutes">۰۰</span>
                                <span class="countdown-label">دقیقه</span>
                            </div>
                            <span class="countdown-separator" aria-hidden="true">:</span>
                            <div class="countdown-cell">
                                <span class="countdown-value" data-unit="seconds">۰۰</span>
                                <span class="countdown-label">ثانیه</span>
                            </div>
                        </div>

                        <div class="mt-6">
                            <div class="flex w-full items-center justify-between gap-2 text-xs font-semibold">
                                <span class="text-muted">ظرفیت جشنواره</span>
                                <span class="shrink-0 text-accent">۷۸٪ تکمیل شده</span>
                            </div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-red-100">
                                <div class="h-full w-[78%] rounded-full bg-linear-to-l from-accent to-accent-deep"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Categories --}}
    <section id="categories" class="pt-16">
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <span class="eyebrow">دسته‌بندی‌ها</span>
                    <h2 class="section-title mt-3">از کجا شروع کنیم؟</h2>
                    <p class="section-text max-w-lg">دسته‌بندی محبوب مشتریان ما برای خرید سریع‌تر و راحت‌تر.</p>
                </div>
                <a href="{{ route('products.index') }}" class="hidden items-center gap-2 text-sm font-bold text-brand-deep transition hover:text-brand-strong sm:inline-flex">
                    همه محصولات
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                </a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-3 sm:gap-4 lg:grid-cols-5">
                @foreach ($categories as $category)
                    @php
                        $icon = $categoryIcons[$loop->index % count($categoryIcons)];
                    @endphp
                    <a href="{{ route('products.index', ['category' => $category->id]) }}"
                        class="group relative flex flex-col overflow-hidden rounded-3xl border border-line bg-linear-to-br from-brand-soft/70 via-white to-white p-5 shadow-card transition-all duration-300 hover:-translate-y-1.5 hover:border-brand hover:shadow-card-hover">
                        <span class="pointer-events-none absolute -end-10 -top-10 size-28 rounded-full bg-brand/10 transition-transform duration-500 group-hover:scale-150" aria-hidden="true"></span>
                        <span class="relative flex size-16 items-center justify-center rounded-2xl bg-white text-brand-deep shadow-sm ring-1 ring-brand/20 transition-colors duration-300 group-hover:bg-brand-strong group-hover:text-white">
                            <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                @if ($icon === 'tshirt')
                                    <path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" />
                                @elseif ($icon === 'hoodie')
                                    <path d="M8 5a4 4 0 0 1 8 0" /><path d="M8 5 4 8l1 4 2-1v9h10v-9l2 1 1-4-4-3" /><path d="M9.5 12h5" />
                                @elseif ($icon === 'dress')
                                    <path d="M9 3h6l-1 4 3 12H7L10 7 9 3Z" /><path d="M9 7h6" />
                                @elseif ($icon === 'pants')
                                    <path d="M7 3h10l1 18h-5l-1-9-1 9H6L7 3Z" /><path d="M7 7h10" />
                                @else
                                    <path d="M3 15c0-3 3-5 6-5h6c3 0 6 2 6 5v3H3v-3Z" /><path d="M8 10 9 6h5l1 4" />
                                @endif
                            </svg>
                        </span>
                        <p class="relative mt-4 text-base font-extrabold text-ink">{{ $category->name }}</p>
                        <p class="relative mt-1 text-[11px] font-medium text-muted">{{ $toPersian($category->products_count) }} محصول</p>
                        <span class="relative mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-brand-deep transition group-hover:gap-2.5">
                            مشاهده محصولات
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Product rows (horizontally scrollable) --}}
    {{-- Best sellers: clean 4-column grid --}}
    <section id="products" class="pt-16">
        <div class="container-page">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <span class="eyebrow">
                        <svg class="size-3.5 text-accent" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5Z" /></svg>
                        محبوب‌ترین‌ها
                    </span>
                    <h2 class="section-title mt-3">پرفروش‌ترین محصولات</h2>
                    <p class="section-text max-w-lg">انتخاب هزاران مشتری؛ محبوب‌ترین‌های این فصل با بهترین امتیاز.</p>
                </div>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-line bg-white px-4 py-2.5 text-sm font-bold text-ink transition hover:border-brand hover:text-brand-deep">
                    همه محصولات
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                </a>
            </div>

            <div class="mt-8 grid grid-cols-2 gap-3.5 sm:gap-5 lg:grid-cols-4">
                @foreach ($bestSellers as $product)
                    @include('partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </div>
    </section>

    {{-- Flash deals --}}
    @include('partials.flash-deals', ['discounted' => $discounted])

    {{-- Editorial: shop by look --}}
    @include('partials.editorial-banner', ['looks' => $products])

    {{-- Newest: horizontal carousel --}}
    @include('partials.product-row', [
        'rowEyebrow' => 'تازه رسید',
        'rowTitle' => 'جدیدترین محصولات',
        'rowProducts' => $newest,
    ])

    {{-- Promotional banners --}}
    <section class="pt-16">
        <div class="container-page grid items-stretch gap-4 lg:grid-cols-5">
            <div class="relative flex flex-col overflow-hidden rounded-[2rem] bg-linear-to-br from-brand-strong via-brand-strong to-brand p-7 text-white shadow-glow sm:p-10 lg:col-span-3">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="absolute -end-10 -top-14 size-56 rounded-full border-[26px] border-white/15"></div>
                    <div class="absolute -bottom-16 end-24 size-40 rounded-full bg-white/10 blur-xl"></div>
                    <div class="absolute inset-y-0 start-0 w-1/2 opacity-20 [background-image:radial-gradient(#ffffff_1px,transparent_1px)] [background-size:20px_20px] [mask-image:linear-gradient(to_right,black,transparent)]"></div>
                </div>

                <div class="relative max-w-md">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-xs font-bold text-white backdrop-blur">
                        <span class="size-1.5 rounded-full bg-white"></span>
                        تازه رسید
                    </span>
                    <h2 class="mt-4 text-2xl font-extrabold leading-[1.45] sm:text-3xl">
                        کالکشن زمستانی آسمان‌پوش
                        <span class="block text-white/80">گرم، سبک و بی‌زحمت</span>
                    </h2>
                    <p class="mt-3 text-sm leading-7 text-white/85">
                        پالتوها و بافت‌های تازه با رنگ‌های آبی و خنثی؛ برای صبح‌های سرد و روزهای کاری.
                    </p>
                    <a href="{{ route('products.index') }}" class="btn btn-white mt-6">
                        دیدن کالکشن زمستانی
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M19 12H5M12 19l-7-7 7-7" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="relative flex flex-col overflow-hidden rounded-[2rem] border border-line bg-white p-7 shadow-card sm:p-10 lg:col-span-2">
                <div class="pointer-events-none absolute -end-12 -bottom-12 size-44 rounded-full bg-brand-soft/70 blur-2xl" aria-hidden="true"></div>

                <div class="relative flex flex-1 flex-col">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-brand-soft px-3 py-1.5 text-xs font-extrabold text-brand-deep">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" />
                        </svg>
                        خبرنامه آسمان‌پوش
                    </span>

                    <h2 class="mt-4 text-2xl font-extrabold leading-[1.45] text-ink">
                        عضو خبرنامه شوید
                        <span class="block text-brand-deep">۱۰٪ تخفیف اولین خرید</span>
                    </h2>
                    <p class="mt-3 text-sm leading-7 text-muted">
                        از کالکشن‌های تازه، حراج‌ها و کدهای تخفیف زودتر از همه باخبر شوید.
                    </p>

                    <form class="mt-auto pt-6" onsubmit="event.preventDefault(); this.reset();">
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input type="email" required placeholder="ایمیل شما" class="field flex-1" dir="ltr" aria-label="ایمیل برای عضویت در خبرنامه">
                            <button type="submit" class="btn btn-primary shrink-0">عضویت</button>
                        </div>
                        <p class="mt-2 text-[11px] text-muted">با عضویت، قوانین حریم خصوصی را می‌پذیرید.</p>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
