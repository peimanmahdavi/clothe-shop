{{-- Horizontally scrollable product row. Expects: $rowTitle, $rowEyebrow, $rowProducts --}}
<section class="pt-16">
    <div class="container-page">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                @isset($rowEyebrow)
                    <span class="eyebrow">{{ $rowEyebrow }}</span>
                @endisset
                <h2 class="section-title mt-3">{{ $rowTitle }}</h2>
            </div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 rounded-2xl border border-line bg-white px-4 py-2.5 text-sm font-bold text-ink transition hover:border-brand hover:text-brand-deep">
                همه محصولات
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
            </a>
        </div>

        <div class="mt-8 flex snap-x snap-mandatory gap-4 overflow-x-auto pb-4 [scrollbar-width:thin]">
            @foreach ($rowProducts as $product)
                <div class="w-60 shrink-0 snap-start sm:w-64">
                    @include('partials.product-card', ['product' => $product])
                </div>
            @endforeach

            {{-- View-all card at the end of the scroll --}}
            <a href="{{ route('products.index') }}"
                class="flex w-60 shrink-0 snap-start flex-col items-center justify-center gap-3 rounded-2xl border border-dashed border-brand/40 bg-brand-soft/30 p-6 text-center transition hover:border-brand hover:bg-brand-soft/60 sm:w-64">
                <span class="flex size-14 items-center justify-center rounded-2xl bg-brand-soft text-brand-deep">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>
                </span>
                <span class="text-sm font-extrabold text-ink">مشاهده همه محصولات</span>
            </a>
        </div>
    </div>
</section>
