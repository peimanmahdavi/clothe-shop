@extends('layouts.app')

@section('title', $product->name . ' | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
        $shots = $product->images;
        $hasMultipleShots = $shots->count() > 1;
    @endphp

    <section class="py-8 sm:py-10">
        <div class="container-page">
            <nav class="mb-6 flex flex-wrap items-center gap-1.5 text-xs text-muted" aria-label="مسیر">
                <a href="{{ route('home') }}" class="transition hover:text-brand-deep">خانه</a>
                <span class="text-line">/</span>
                <a href="{{ route('products.index') }}" class="transition hover:text-brand-deep">محصولات</a>
                @if ($product->category)
                    <span class="text-line">/</span>
                    <a href="{{ route('products.index', ['category' => $product->category->id]) }}" class="transition hover:text-brand-deep">{{ $product->category->name }}</a>
                @endif
                <span class="text-line">/</span>
                <span class="font-semibold text-ink">{{ $product->name }}</span>
            </nav>

            <div class="grid items-start gap-6 lg:grid-cols-2 lg:gap-10">
                {{-- Gallery (right column in RTL) --}}
                <div class="space-y-3">
                    <div class="admin-card overflow-hidden p-3">
                        <div class="relative w-full overflow-hidden rounded-2xl bg-canvas" style="aspect-ratio: 1 / 1;">
                            @if ($product->discountPercent() > 0)
                                <span class="badge-sale absolute top-3 start-3 z-20">{{ $toPersian($product->discountPercent()) }}٪ تخفیف</span>
                            @endif

                            @if ($shots->isNotEmpty())
                                <img id="product-main-image"
                                    src="{{ $shots->first()->url() }}"
                                    alt="{{ $product->name }}"
                                    class="absolute inset-0 h-full w-full object-contain p-2 sm:p-4"
                                    width="600" height="600">
                            @else
                                <div class="absolute inset-0 flex items-center justify-center text-brand/50">
                                    <svg class="size-24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true">
                                        <path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" />
                                    </svg>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if ($hasMultipleShots)
                        <div class="flex gap-2 overflow-x-auto pb-1 [scrollbar-width:thin]" data-thumbs>
                            @foreach ($shots as $image)
                                <button type="button"
                                    class="product-thumb relative aspect-square w-16 shrink-0 overflow-hidden rounded-xl border-2 bg-canvas transition @if ($loop->first) border-brand @else border-transparent hover:border-brand/50 @endif"
                                    data-thumb
                                    data-src="{{ $image->url() }}"
                                    aria-label="نمایش تصویر {{ $toPersian($loop->iteration) }} {{ $product->name }}">
                                    <img src="{{ $image->url() }}" alt="{{ $product->name }} — تصویر {{ $toPersian($loop->iteration) }}" class="absolute inset-0 h-full w-full object-contain p-1" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Info (left column in RTL) --}}
                <div class="space-y-5">
                    <div>
                        <p class="text-sm font-medium text-muted">{{ $product->brand?->name ?? $product->category?->name }}</p>
                        <h1 class="mt-1 text-2xl font-extrabold leading-9 text-ink sm:text-3xl sm:leading-10">{{ $product->name }}</h1>
                        <div class="mt-2 flex items-center gap-2 text-sm">
                            <span class="flex items-center gap-1 font-bold text-ink">
                                <svg class="size-4 text-amber-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="m12 3 2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1L3.2 9.5l6.1-.9L12 3Z" />
                                </svg>
                                {{ $toPersian(number_format((float) $product->rating, 1)) }}
                            </span>
                            <span class="text-muted">({{ $toPersian($product->reviews_count) }} نظر)</span>
                        </div>
                    </div>

                    <div class="admin-card p-5">
                        <div class="flex flex-wrap items-end gap-3">
                            <span class="text-2xl font-extrabold text-ink sm:text-3xl">
                                {{ $toman($product->finalPrice()) }}
                                <span class="text-sm font-medium text-muted">تومان</span>
                            </span>
                            @if ($product->discountPercent() > 0)
                                <span class="mb-1 text-sm font-medium text-muted line-through">{{ $toman($product->price) }}</span>
                                <span class="badge-sale mb-1">{{ $toPersian($product->discountPercent()) }}٪</span>
                            @endif
                        </div>

                        <div class="mt-3">
                            @if ($product->stock === 0)
                                <span class="status-pill bg-red-100 text-red-700">ناموجود</span>
                            @elseif ($product->stock <= 5)
                                <span class="status-pill bg-amber-100 text-amber-700">تنها {{ $toPersian($product->stock) }} عدد باقی مانده</span>
                            @else
                                <span class="status-pill bg-green-100 text-green-700">موجود در انبار</span>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('cart.add') }}" class="mt-5 space-y-4">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <input type="hidden" name="quantity" value="1">

                            @if (!empty($product->sizes))
                                <div>
                                    <p class="mb-2 text-xs font-bold text-ink">سایز</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($product->sizes as $i => $size)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="size" value="{{ $size }}" class="peer sr-only" @checked($i === 0) required>
                                                <span class="inline-flex h-10 min-w-12 items-center justify-center rounded-xl border border-line px-3 text-sm font-bold text-ink transition hover:border-brand/60 peer-checked:border-brand peer-checked:bg-brand-soft peer-checked:text-brand-deep">{{ $size }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if (!empty($product->colors))
                                <div>
                                    <p class="mb-2 text-xs font-bold text-ink">رنگ</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($product->colors as $i => $color)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="color" value="{{ $color }}" class="peer sr-only" @checked($i === 0) required>
                                                <span class="inline-flex h-10 items-center justify-center rounded-xl border border-line px-4 text-sm font-bold text-ink transition hover:border-brand/60 peer-checked:border-brand peer-checked:bg-brand-soft peer-checked:text-brand-deep">{{ $color }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="flex flex-col gap-2 pt-1 sm:flex-row">
                                <button type="submit" name="checkout" value="1" class="btn btn-primary flex-1 @if ($product->stock === 0) pointer-events-none opacity-50 @endif">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8h12l-1 12H7L6 8Z" /><path d="M9 8a3 3 0 0 1 6 0" /></svg>
                                    افزودن و ثبت سفارش
                                </button>
                                <button type="submit" class="btn btn-outline flex-1 @if ($product->stock === 0) pointer-events-none opacity-50 @endif">
                                    افزودن به سبد
                                </button>
                            </div>
                        </form>
                    </div>

                    @if ($product->description)
                        <div class="admin-card p-5">
                            <h2 class="mb-2 text-sm font-extrabold text-ink">توضیحات</h2>
                            <p class="text-sm leading-7 text-muted">{{ $product->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            @if ($related->isNotEmpty())
                <div class="mt-12">
                    <div class="mb-6 flex items-end justify-between gap-3">
                        <h2 class="section-title">محصولات مرتبط</h2>
                        <a href="{{ route('products.index') }}" class="hidden items-center gap-1.5 text-sm font-bold text-brand-deep transition hover:text-brand-strong sm:inline-flex">
                            مشاهده همه
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M19 12H5M12 19l-7-7 7-7" />
                            </svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-3.5 sm:gap-5 lg:grid-cols-4">
                        @foreach ($related as $product)
                            @include('partials.product-card', ['product' => $product])
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const main = document.getElementById('product-main-image');
        const thumbs = document.querySelectorAll('[data-thumb]');
        if (!main || thumbs.length === 0) return;

        thumbs.forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                const src = thumb.dataset.src;
                if (src) main.src = src;

                thumbs.forEach(function (t) {
                    t.classList.remove('border-brand');
                    t.classList.add('border-transparent');
                });
                thumb.classList.remove('border-transparent');
                thumb.classList.add('border-brand');
            });
        });
    });
</script>
@endpush
