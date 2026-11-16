@extends('layouts.app')

@section('title', 'همه محصولات | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
        $sortOptions = [
            'newest' => 'جدیدترین',
            'popular' => 'محبوب‌ترین',
            'rating' => 'بیشترین امتیاز',
            'price_asc' => 'ارزان‌ترین',
            'price_desc' => 'گران‌ترین',
        ];
    @endphp

    <section class="border-b border-line bg-linear-to-b from-brand-soft/60 to-canvas">
        <div class="container-page py-10">
            <nav class="mb-3 flex items-center gap-1.5 text-xs text-muted" aria-label="مسیر">
                <a href="{{ route('home') }}" class="transition hover:text-brand-deep">خانه</a>
                <span>/</span>
                <span class="font-semibold text-ink">محصولات</span>
            </nav>
            <h1 class="text-2xl font-extrabold text-ink sm:text-3xl">همه محصولات</h1>
            <p class="mt-2 max-w-xl text-sm leading-7 text-muted">
                جست‌وجو کنید و با فیلتر دسته‌بندی، برند و قیمت، محصول دلخواه خود را پیدا کنید.
            </p>
        </div>
    </section>

    <section class="py-8">
        <div class="container-page">
            <form method="GET" action="{{ route('products.index') }}" id="filter-form">
                {{-- Always-visible search + sort + mobile filter toggle --}}
                <div class="mb-6 flex flex-wrap items-center gap-3">
                    <div class="relative min-w-[200px] flex-1">
                        <svg class="pointer-events-none absolute inset-y-0 start-3.5 my-auto size-4.5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
                        </svg>
                        <input type="search" id="q" name="q" value="{{ $filters['q'] }}" class="search-input" placeholder="جست‌وجوی محصول..." aria-label="جست‌وجوی محصول">
                    </div>

                    <button type="button" id="filters-toggle" class="btn btn-outline lg:hidden" aria-controls="filters-panel" aria-expanded="false" data-open-label="فیلترها" data-close-label="بستن فیلترها">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
                        <span data-toggle-label>فیلترها</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <label for="sort" class="hidden text-sm text-muted sm:block">مرتب‌سازی:</label>
                        <select id="sort" name="sort" class="field w-auto py-2" onchange="this.form.submit()">
                            @foreach ($sortOptions as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid gap-8 lg:grid-cols-[280px_1fr]">
                    {{-- Filters: right side on desktop (first column in RTL), toggle panel on mobile --}}
                    <aside id="filters-panel" class="hidden space-y-4 lg:block">
                        <div class="flex items-center justify-between lg:hidden">
                            <h2 class="text-sm font-extrabold text-ink">فیلترها</h2>
                            <button type="button" class="icon-button" data-close-filters aria-label="بستن فیلترها">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                            </button>
                        </div>

                        <div class="admin-card p-4">
                            <h2 class="mb-3 text-sm font-bold text-ink">دسته‌بندی</h2>
                            <div class="space-y-2">
                                <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                                    <input type="radio" name="category" value="" @checked(blank($filters['category'])) class="text-brand focus:ring-brand">
                                    همه دسته‌ها
                                </label>
                                @foreach ($categories as $category)
                                    <label class="flex cursor-pointer items-center justify-between gap-2 text-sm text-muted">
                                        <span class="flex items-center gap-2">
                                            <input type="radio" name="category" value="{{ $category->id }}" @checked((string) $filters['category'] === (string) $category->id) class="text-brand focus:ring-brand">
                                            {{ $category->name }}
                                        </span>
                                        <span class="text-[11px] text-muted">{{ $toPersian($category->products_count) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="admin-card p-4">
                            <h2 class="mb-3 text-sm font-bold text-ink">برند</h2>
                            <div class="space-y-2">
                                <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                                    <input type="radio" name="brand" value="" @checked(blank($filters['brand'])) class="text-brand focus:ring-brand">
                                    همه برندها
                                </label>
                                @foreach ($brands as $brand)
                                    <label class="flex cursor-pointer items-center gap-2 text-sm text-muted">
                                        <input type="radio" name="brand" value="{{ $brand->id }}" @checked((string) $filters['brand'] === (string) $brand->id) class="text-brand focus:ring-brand">
                                        {{ $brand->name }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="admin-card p-4">
                            <h2 class="mb-3 text-sm font-bold text-ink">محدوده قیمت (تومان)</h2>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" name="min_price" value="{{ $filters['min_price'] }}" class="field" placeholder="از" min="0">
                                <input type="number" name="max_price" value="{{ $filters['max_price'] }}" class="field" placeholder="تا" min="0">
                            </div>
                            <label class="mt-3 flex cursor-pointer items-center gap-2 text-sm text-muted">
                                <input type="checkbox" name="in_stock" value="1" @checked($filters['in_stock']) class="rounded text-brand focus:ring-brand">
                                فقط کالاهای موجود
                            </label>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit" class="btn btn-primary flex-1">اعمال فیلتر</button>
                            <a href="{{ route('products.index') }}" class="btn btn-outline">حذف</a>
                        </div>
                    </aside>

                    {{-- Results --}}
                    <div>
                        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-sm text-muted">
                                <span class="font-bold text-ink">{{ $toPersian($products->total()) }}</span> محصول یافت شد
                            </p>
                        </div>

                        @if ($products->isEmpty())
                            <div class="admin-card p-12 text-center">
                                <div class="mx-auto mb-3 flex size-16 items-center justify-center rounded-xl bg-canvas text-muted">
                                    <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" />
                                    </svg>
                                </div>
                                <h3 class="font-bold text-ink">محصولی با این فیلترها پیدا نشد</h3>
                                <p class="mt-1 text-sm text-muted">فیلترها را تغییر دهید یا عبارت دیگری جست‌وجو کنید.</p>
                                <a href="{{ route('products.index') }}" class="btn btn-primary mt-4 inline-flex">مشاهده همه محصولات</a>
                            </div>
                        @else
                            <div class="grid grid-cols-2 gap-3.5 sm:gap-5 lg:grid-cols-3">
                                @foreach ($products as $product)
                                    @include('partials.product-card', ['product' => $product])
                                @endforeach
                            </div>

                            <div class="mt-8">
                                {{ $products->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('filters-toggle');
        const panel = document.getElementById('filters-panel');
        const label = toggle ? toggle.querySelector('[data-toggle-label]') : null;
        if (!toggle || !panel) return;

        const isDesktop = () => window.matchMedia('(min-width: 1024px)').matches;

        const setOpen = (open) => {
            panel.classList.toggle('hidden', !open);
            toggle.setAttribute('aria-expanded', String(open));
            if (label) {
                label.textContent = open ? toggle.dataset.closeLabel : toggle.dataset.openLabel;
            }
        };

        // Collapsed on mobile, always visible on desktop.
        if (!isDesktop()) {
            setOpen(false);
        }

        toggle.addEventListener('click', function () {
            setOpen(panel.classList.contains('hidden'));
        });

        document.querySelectorAll('[data-close-filters]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setOpen(false);
            });
        });

        window.addEventListener('resize', function () {
            if (isDesktop()) {
                panel.classList.remove('hidden');
                toggle.setAttribute('aria-expanded', 'false');
                if (label) label.textContent = toggle.dataset.openLabel;
            }
        });
    });
</script>
@endpush