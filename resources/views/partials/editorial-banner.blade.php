@php
    $looks = $looks ?? collect();
@endphp

<section class="pt-16">
    <div class="container-page">
        <div class="relative overflow-hidden rounded-[2rem] bg-ink text-white">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -end-20 -top-20 size-72 rounded-full bg-brand-strong/30 blur-3xl"></div>
                <div class="absolute -bottom-24 start-1/4 size-64 rounded-full bg-accent/20 blur-3xl"></div>
            </div>

            <div class="relative grid items-center gap-8 p-6 sm:p-10 lg:grid-cols-2">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs font-bold text-white backdrop-blur">
                        <span class="size-1.5 rounded-full bg-brand"></span>
                        ست کردن استایل
                    </span>
                    <h2 class="mt-4 text-2xl font-extrabold leading-[1.5] sm:text-3xl sm:leading-[1.45]">
                        با «استایل‌های آسمان‌پوش»
                        <span class="block text-white/80">ترکیب‌های آماده برای هر روز</span>
                    </h2>
                    <p class="mt-3 max-w-md text-sm leading-7 text-white/70">
                        ست‌های کامل از پیراهن، کفش و اکسسوری را ببینید؛ همان‌طور که استایلیست‌های ما کنار هم چیده‌اند. یک کلیک تا استایل کامل.
                    </p>
                    <a href="{{ route('products.index') }}" class="btn btn-primary mt-6">
                        مشاهده استایل‌ها
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                    </a>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    @forelse ($looks->take(3) as $product)
                        @php($img = $product->images->first())
                        <a href="{{ route('products.show', $product) }}"
                            class="group relative aspect-[3/4] overflow-hidden rounded-2xl border border-white/10 bg-white/5">
                            @if ($img)
                                <img src="{{ $img->url() }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-white/30">
                                    <svg class="size-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" /></svg>
                                </div>
                            @endif
                            <span class="absolute inset-x-0 bottom-0 bg-linear-to-t from-ink/90 to-transparent p-2 text-[10px] font-bold leading-4 text-white">
                                {{ $product->name }}
                            </span>
                        </a>
                    @empty
                        <div class="col-span-3 rounded-2xl border border-dashed border-white/20 p-6 text-center text-sm text-white/60">
                            به‌زودی استایل‌های جدید اضافه می‌شود.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</section>
