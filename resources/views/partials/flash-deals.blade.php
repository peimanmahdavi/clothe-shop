@php
    $toPersian = $toPersian ?? static fn (string|int $value): string => strtr((string) $value, [
        '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
        '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
    ]);
    $toman = $toman ?? static fn (int $amount): string => $toPersian(number_format($amount));
@endphp

<section id="flash-deals" class="pt-16">
    <div class="container-page">
        <div class="relative overflow-hidden rounded-[2rem] border border-accent/20 bg-linear-to-b from-accent-soft/80 to-white p-5 sm:p-8">
            <div class="pointer-events-none absolute -end-16 -top-16 size-56 rounded-full bg-accent/10 blur-3xl" aria-hidden="true"></div>

            <div class="relative mb-6 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-accent px-3 py-1.5 text-xs font-extrabold text-white shadow-lg shadow-accent/30">
                        <svg class="size-3.5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M13 2 4.5 13.5H11l-1 8.5 8.5-11.5H12l1-8.5Z" /></svg>
                        پیشنهاد شگفت‌انگیز
                    </span>
                    <h2 class="mt-3 text-2xl font-extrabold text-ink sm:text-3xl">محصولات تخفیف‌دار</h2>
                    <p class="mt-1 text-sm text-muted">تخفیف‌های ویژه با موجودی محدود — تا پایان موجودی!</p>
                </div>

                <div class="flex items-center gap-2">
                    <a href="{{ route('products.index', ['sort' => 'price_asc']) }}" class="hidden items-center gap-2 rounded-2xl border border-accent/30 bg-white px-4 py-2.5 text-sm font-bold text-accent transition hover:bg-accent hover:text-white sm:inline-flex">
                        همه تخفیف‌ها
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                    </a>

                    {{-- RTL scroll arrows (top-left on the header) --}}
                    <div class="flex items-center gap-2" data-flash-controls>
                        <button type="button" data-flash-prev class="icon-button" aria-label="قبلی">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 6 6 6-6 6" /></svg>
                        </button>
                        <button type="button" data-flash-next class="icon-button" aria-label="بعدی">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 6-6 6 6 6" /></svg>
                        </button>
                    </div>
                </div>
            </div>

            <div class="relative" data-flash-wrap>
                <div data-flash-track class="no-scrollbar flex snap-x snap-mandatory gap-4 overflow-x-auto scroll-smooth pb-2">
                    @foreach ($discounted as $product)
                        <div class="w-60 shrink-0 snap-start sm:w-64">
                            @include('partials.product-card', ['product' => $product])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

@once
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-flash-wrap]').forEach(function (wrap) {
                const track = wrap.querySelector('[data-flash-track]');
                const controls = wrap.closest('section')?.querySelector('[data-flash-controls]');
                if (!track || !controls) return;

                const step = () => Math.max(track.clientWidth * 0.8, 240);
                const prev = controls.querySelector('[data-flash-prev]');
                const next = controls.querySelector('[data-flash-next]');

                prev?.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));
                next?.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
            });
        });
    </script>
    @endpush
@endonce
