@php
    $toPersian = $toPersian ?? static fn (string|int $value): string => strtr((string) $value, [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ]);
    $toman = $toman ?? static fn (int $amount): string => $toPersian(number_format($amount));

    $shots = $product->images;
    $primary = $shots->first();
    $secondary = $shots->skip(1)->first();
    $colors = collect($product->colors ?? []);
    $hasDiscount = $product->discountPercent() > 0;
    $remaining = max((int) $product->stock, 0);
    $capacity = max($remaining, 20);
    $soldPercent = (int) round((($capacity - $remaining) / $capacity) * 100);
@endphp

<article class="product-card group flex h-full flex-col">
    <div class="relative aspect-[4/5] overflow-hidden bg-canvas">
        <a href="{{ route('products.show', $product) }}" class="absolute inset-0 z-10" aria-label="مشاهده {{ $product->name }}"></a>

        @if ($hasDiscount)
            <span class="badge-sale absolute top-3 start-3 z-20">{{ $toPersian($product->discountPercent()) }}٪<span class="sr-only">تخفیف</span></span>
        @endif

        @if ($product->stock === 0)
            <span class="absolute top-3 end-3 z-20 rounded-full bg-ink/80 px-2.5 py-1 text-[10px] font-extrabold text-white">ناموجود</span>
        @endif

        @if ($primary)
            <img src="{{ $primary->url() }}" alt="{{ $product->name }}"
                class="absolute inset-0 h-full w-full object-cover transition-all duration-500 ease-out group-hover:scale-105 {{ $secondary ? 'group-hover:opacity-0' : '' }}"
                width="400" height="500" loading="lazy">
            @if ($secondary)
                <img src="{{ $secondary->url() }}" alt="{{ $product->name }} — نمای دوم"
                    class="absolute inset-0 h-full w-full object-cover opacity-0 transition-all duration-500 ease-out group-hover:scale-105 group-hover:opacity-100"
                    width="400" height="500" loading="lazy">
            @endif
        @else
            <div class="absolute inset-0 flex items-center justify-center text-brand/40">
                <svg class="size-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                    <path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" />
                </svg>
            </div>
        @endif

        {{-- Hover quick actions --}}
        <div class="absolute inset-x-0 bottom-0 z-20 translate-y-full p-2.5 transition-transform duration-300 ease-out group-hover:translate-y-0">
            <a href="{{ route('products.show', $product) }}"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-ink/90 px-3 py-2.5 text-xs font-bold text-white shadow-lg backdrop-blur transition hover:bg-brand-strong">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" /><circle cx="12" cy="12" r="3" />
                </svg>
                مشاهده سریع
            </a>
        </div>
    </div>

    <div class="flex flex-1 flex-col gap-1.5 p-3.5 sm:p-4">
        <div class="flex items-center justify-between gap-2 text-[11px]">
            <span class="truncate text-muted">{{ $product->brand?->name ?? $product->category?->name }}</span>
            <span class="flex shrink-0 items-center gap-1 font-bold text-ink">
                <svg class="size-3.5 text-amber-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3Z" />
                </svg>
                {{ $toPersian(number_format((float) $product->rating, 1)) }}
                <span class="font-medium text-muted">({{ $toPersian($product->reviews_count) }})</span>
            </span>
        </div>

        <h3 class="line-clamp-2 min-h-[3rem] text-sm font-bold leading-6 text-ink">
            <a href="{{ route('products.show', $product) }}" class="hover:text-brand-deep">{{ $product->name }}</a>
        </h3>

        @if ($colors->isNotEmpty())
            <div class="flex items-center gap-1.5 pt-0.5">
                @foreach ($colors->take(5) as $color)
                    <span class="size-3 rounded-full border border-line bg-canvas" title="{{ $color }}" aria-label="رنگ {{ $color }}"></span>
                @endforeach
                @if ($colors->count() > 5)
                    <span class="text-[10px] font-bold text-muted">+{{ $toPersian($colors->count() - 5) }}</span>
                @endif
            </div>
        @endif

        <div class="mt-auto pt-2">
            @if ($hasDiscount)
                <p class="text-[11px] font-medium text-muted line-through">{{ $toman($product->price) }}</p>
            @endif
            <p class="text-base font-extrabold text-ink">
                {{ $toman($product->finalPrice()) }}
                <span class="text-[10px] font-medium text-muted">تومان</span>
            </p>
        </div>

        @if ($hasDiscount)
            <div class="pt-1">
                <div class="mb-1 flex items-center justify-between text-[10px] font-bold">
                    <span class="text-accent">{{ $toPersian($soldPercent) }}٪ فروش رفت</span>
                    <span class="text-muted">{{ $toPersian($remaining) }} عدد</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-accent-soft">
                    <div class="h-full rounded-full bg-linear-to-l from-accent to-accent-deep" style="width: {{ max($soldPercent, 5) }}%"></div>
                </div>
            </div>
        @endif

        <button type="button"
            class="mt-2 flex w-full items-center justify-center gap-1.5 rounded-xl bg-brand-soft px-3 py-2.5 text-xs font-extrabold text-brand-deep transition hover:bg-brand-strong hover:text-white disabled:pointer-events-none disabled:opacity-40"
            data-add-to-cart @disabled($product->stock === 0)
            aria-label="افزودن {{ $product->name }} به سبد خرید">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M6 8h12l-1 12H7L6 8Z" /><path d="M9 8a3 3 0 0 1 6 0" />
            </svg>
            افزودن به سبد
        </button>
    </div>
</article>
