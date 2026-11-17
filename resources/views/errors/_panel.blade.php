@php
    $toPersian = static fn (string|int $value): string => strtr((string) $value, [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ]);

    $statusCode = (int) ($status ?? 0);

    if ($statusCode === 0 && isset($exception) && is_object($exception) && method_exists($exception, 'getStatusCode')) {
        $statusCode = (int) $exception->getStatusCode();
    }

    if ($statusCode < 100 || $statusCode > 599) {
        $statusCode = 500;
    }

    $catalog = [
        400 => ['title' => 'درخواست نامعتبر', 'message' => 'درخواست شما قابل پردازش نیست. لطفاً دوباره تلاش کنید.', 'icon' => 'alert'],
        401 => ['title' => 'ورود لازم است', 'message' => 'برای دسترسی به این بخش ابتدا وارد حساب کاربری خود شوید.', 'icon' => 'lock'],
        403 => ['title' => 'دسترسی غیرمجاز', 'message' => 'شما اجازه دسترسی به این صفحه را ندارید.', 'icon' => 'lock'],
        404 => ['title' => 'صفحه پیدا نشد', 'message' => 'صفحه‌ای که به دنبال آن هستید وجود ندارد یا جابه‌جا شده است.', 'icon' => 'search'],
        405 => ['title' => 'درخواست مجاز نیست', 'message' => 'این نوع درخواست برای این صفحه پشتیبانی نمی‌شود.', 'icon' => 'alert'],
        419 => ['title' => 'صفحه منقضی شده', 'message' => 'نشست شما منقضی شده است. لطفاً صفحه را دوباره بارگذاری کنید.', 'icon' => 'clock'],
        429 => ['title' => 'درخواست‌های بیش از حد', 'message' => 'تعداد درخواست‌های شما زیاد است. لطفاً کمی بعد دوباره تلاش کنید.', 'icon' => 'clock'],
        500 => ['title' => 'خطای سرور', 'message' => 'مشکلی در سرور رخ داده است. لطفاً کمی بعد دوباره تلاش کنید.', 'icon' => 'warning'],
        503 => ['title' => 'موقتاً در دسترس نیست', 'message' => 'فروشگاه در حال به‌روزرسانی است. لطفاً بعداً مراجعه کنید.', 'icon' => 'warning'],
    ];

    $meta = $catalog[$statusCode] ?? ['title' => 'خطایی رخ داد', 'message' => 'متأسفانه مشکلی پیش آمد. لطفاً دوباره تلاش کنید.', 'icon' => 'warning'];

    // The backend may pass the error through with('error', ...) on the view or a redirect.
    $provided = $error ?? session('error');

    if (is_array($provided)) {
        $meta['title'] = $provided['title'] ?? $meta['title'];
        $meta['message'] = $provided['message'] ?? $meta['message'];
        $meta['hint'] = $provided['hint'] ?? null;
        $statusCode = (int) ($provided['status'] ?? $statusCode);
    } elseif (is_string($provided) && trim($provided) !== '') {
        $meta['message'] = $provided;
    } elseif (is_object($provided) && method_exists($provided, 'getMessage') && trim((string) $provided->getMessage()) !== '') {
        $meta['message'] = $provided->getMessage();
    }

    // Explicit title/message passed to the view win over the catalog defaults.
    if (isset($title) && is_string($title) && trim($title) !== '') {
        $meta['title'] = $title;
    }

    if (isset($message) && is_string($message) && trim($message) !== '') {
        $meta['message'] = $message;
    }

    if (isset($hint) && is_string($hint) && trim($hint) !== '') {
        $meta['hint'] = $hint;
    }

    $statusLabel = $toPersian($statusCode);
@endphp

<section class="relative isolate overflow-hidden bg-linear-to-b from-brand-soft/70 via-canvas to-canvas">
    <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
        <div class="absolute -end-28 -top-28 size-96 rounded-full bg-brand/20 blur-3xl"></div>
        <div class="absolute -start-32 top-1/3 size-80 rounded-full bg-brand-strong/10 blur-3xl"></div>
        <div class="absolute inset-0 opacity-30 [background-image:radial-gradient(#38bdf8_1px,transparent_1px)] [background-size:26px_26px] [mask-image:radial-gradient(ellipse_at_center,black,transparent_70%)]"></div>
    </div>

    <div class="container-page relative flex min-h-[72vh] flex-col items-center justify-center py-16 text-center sm:py-24">
        <span class="badge-soft gap-2">
            <span class="size-1.5 rounded-full bg-brand-strong"></span>
            خطای {{ $statusLabel }}
        </span>

        <div class="relative mt-8">
            <div class="pointer-events-none absolute inset-0 -z-10 rounded-full bg-brand/25 blur-2xl" aria-hidden="true"></div>
            <div class="flex size-24 items-center justify-center rounded-[2rem] bg-linear-to-br from-brand to-brand-strong text-white shadow-glow sm:size-28">
                <svg class="size-12 sm:size-14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    @switch($meta['icon'])
                        @case('search')
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                            <path d="M8.5 11h5M11 8.5v5" />
                            @break
                        @case('lock')
                            <rect x="4" y="10" width="16" height="10" rx="2.5" />
                            <path d="M8 10V7a4 4 0 0 1 8 0v3" />
                            <path d="M12 14v2" />
                            @break
                        @case('clock')
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 7v5l3 2" />
                            @break
                        @default
                            <path d="M12 8v5M12 17h.01" />
                            <path d="M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                            @break
                    @endswitch
                </svg>
            </div>
        </div>

        <p class="mt-8 text-6xl font-black leading-none tracking-tight text-ink/10 sm:text-8xl" aria-hidden="true">{{ $statusLabel }}</p>
        <h1 class="-mt-3 text-2xl font-extrabold leading-tight text-ink sm:text-4xl">{{ $meta['title'] }}</h1>
        <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-muted sm:text-base">{{ $meta['message'] }}</p>

        @isset($meta['hint'])
            <p class="mx-auto mt-3 max-w-xl rounded-xl border border-line bg-white/70 px-4 py-2.5 text-[11px] leading-6 text-muted">
                {{ $meta['hint'] }}
            </p>
        @endisset

        <div class="mt-8 flex w-full flex-col items-center justify-center gap-3 sm:w-auto sm:flex-row">
            <a href="{{ route('home') }}" class="btn btn-primary w-full sm:w-auto">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m3 11 9-8 9 8" />
                    <path d="M5 10v10h14V10" />
                </svg>
                بازگشت به صفحه اصلی
            </a>
            <a href="{{ route('products.index') }}" class="btn btn-outline w-full sm:w-auto">
                مشاهده محصولات
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
            </a>
        </div>

        <div class="mt-10 grid w-full max-w-2xl gap-3 sm:grid-cols-3">
            <a href="{{ route('cart.index') }}" class="group flex items-center justify-center gap-2 rounded-2xl border border-line bg-white/80 px-4 py-3 text-xs font-bold text-ink shadow-sm transition hover:border-brand hover:text-brand-deep">
                <svg class="size-4 text-muted transition group-hover:text-brand-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 8h12l-1 12H7L6 8Z" />
                    <path d="M9 8a3 3 0 0 1 6 0" />
                </svg>
                سبد خرید
            </a>

            @auth
                <a href="{{ route('profile.dashboard') }}" class="group flex items-center justify-center gap-2 rounded-2xl border border-line bg-white/80 px-4 py-3 text-xs font-bold text-ink shadow-sm transition hover:border-brand hover:text-brand-deep">
                    <svg class="size-4 text-muted transition group-hover:text-brand-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 21a8 8 0 0 1 16 0" />
                    </svg>
                    حساب کاربری
                </a>
            @else
                <a href="{{ route('auth.login') }}" class="group flex items-center justify-center gap-2 rounded-2xl border border-line bg-white/80 px-4 py-3 text-xs font-bold text-ink shadow-sm transition hover:border-brand hover:text-brand-deep">
                    <svg class="size-4 text-muted transition group-hover:text-brand-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="12" cy="8" r="4" />
                        <path d="M4 21a8 8 0 0 1 16 0" />
                    </svg>
                    ورود / ثبت‌نام
                </a>
            @endauth

            <a href="{{ route('home') }}#contact" class="group flex items-center justify-center gap-2 rounded-2xl border border-line bg-white/80 px-4 py-3 text-xs font-bold text-ink shadow-sm transition hover:border-brand hover:text-brand-deep">
                <svg class="size-4 text-muted transition group-hover:text-brand-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2Z" />
                </svg>
                تماس با ما
            </a>
        </div>

        <p class="mt-8 text-[11px] leading-6 text-muted">
            اگر مشکل ادامه داشت، با پشتیبانی آسمان‌پوش در ارتباط باشید.
        </p>
    </div>
</section>
