@extends('layouts.app')

@section('title', 'سبد خرید | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
    @endphp

    <section class="py-8 sm:py-10">
        <div class="container-page">
            <div class="mx-auto max-w-5xl">
                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-ink">سبد خرید</h1>
                    <p class="mt-1 text-sm text-muted">کالاهای انتخابی خود را بررسی و سفارش را نهایی کنید</p>
                </div>

                @if (session('status'))
                    <div class="mb-6 flex items-center gap-3 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700" role="status">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="m5 13 4 4L19 7" />
                        </svg>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="alert">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($items->isEmpty())
                    <div class="admin-card p-10 text-center">
                        <div class="mx-auto mb-3 flex size-16 items-center justify-center rounded-2xl bg-brand-soft text-brand-deep">
                            <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M6 8h12l-1 12H7L6 8Z" />
                                <path d="M9 8a3 3 0 0 1 6 0" />
                            </svg>
                        </div>
                        <h2 class="font-bold text-ink">سبد خرید شما خالی است</h2>
                        <p class="mt-1 text-sm text-muted">برای شروع خرید، محصولی را به سبد اضافه کنید</p>
                        <a href="{{ route('products.index') }}" class="btn btn-primary mt-4 inline-flex">مشاهده محصولات</a>
                    </div>
                @else
                    <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
                        {{-- Items --}}
                        <div class="space-y-3" data-cart-items>
                            @foreach ($items as $item)
                                @php $product = $item->product; @endphp
                                <article class="admin-card p-4" data-cart-row data-item-id="{{ $item->id }}">
                                    <div class="flex items-center gap-4">
                                        <div class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-soft text-brand-deep">
                                            @if ($product && $product->images->isNotEmpty())
                                                <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                            @else
                                                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" /></svg>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <a href="{{ $product ? route('products.show', $product) : '#' }}" class="line-clamp-1 font-bold text-ink hover:text-brand-deep">{{ $product?->name ?? 'نامشخص' }}</a>
                                            <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-muted">
                                                @if ($item->size)
                                                    <span class="rounded-lg bg-canvas px-2 py-0.5">سایز: <span class="font-bold text-ink">{{ $item->size }}</span></span>
                                                @endif
                                                @if ($item->color)
                                                    <span class="rounded-lg bg-canvas px-2 py-0.5">رنگ: <span class="font-bold text-ink">{{ $item->color }}</span></span>
                                                @endif
                                            </div>
                                            <p class="mt-1 text-sm font-extrabold text-ink">{{ $toman($item->unit_price) }} <span class="text-[10px] font-medium text-muted">تومان</span></p>
                                        </div>

                                        <button type="button" class="icon-button shrink-0 text-red-600 hover:bg-red-50" data-remove-item aria-label="حذف از سبد">
                                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M3 6h18" /><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6" /><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                            </svg>
                                        </button>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between border-t border-line pt-3">
                                        <div class="inline-flex items-center rounded-xl border border-line bg-white" dir="ltr">
                                            <button type="button" class="flex size-9 items-center justify-center text-lg font-bold text-ink transition hover:text-brand-deep" data-qty-decrease aria-label="کاهش تعداد">−</button>
                                            <input type="number" value="{{ $item->quantity }}" min="1" class="w-12 border-0 bg-transparent text-center text-sm font-bold text-ink focus:outline-none [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none" data-qty-input aria-label="تعداد">
                                            <button type="button" class="flex size-9 items-center justify-center text-lg font-bold text-ink transition hover:text-brand-deep" data-qty-increase aria-label="افزایش تعداد">+</button>
                                        </div>
                                        <p class="text-sm font-extrabold text-ink" data-line-total>{{ $toman($item->lineTotal()) }} <span class="text-[10px] font-medium text-muted">تومان</span></p>
                                    </div>
                                </article>
                            @endforeach

                            <div class="pt-1">
                                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-1.5 text-sm font-bold text-brand-deep transition hover:text-brand-strong">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                                    ادامه خرید
                                </a>
                            </div>
                        </div>

                        {{-- Summary --}}
                        <aside class="lg:sticky lg:top-28 lg:self-start">
                            <div class="admin-card p-5">
                                <h2 class="text-lg font-extrabold text-ink">خلاصه سفارش</h2>
                                <dl class="mt-4 space-y-3 text-sm">
                                    <div class="flex items-center justify-between">
                                        <dt class="text-muted">تعداد اقلام</dt>
                                        <dd class="font-bold text-ink">{{ $toPersian($cart->totalQuantity()) }}</dd>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <dt class="text-muted">جمع کل</dt>
                                        <dd class="font-bold text-ink">{{ $toman($subtotal) }} تومان</dd>
                                    </div>
                                    <div class="flex items-center justify-between border-t border-line pt-3">
                                        <dt class="font-bold text-ink">مبلغ قابل پرداخت</dt>
                                        <dd class="text-lg font-extrabold text-brand-deep">{{ $toman($subtotal) }} <span class="text-[11px] font-medium text-muted">تومان</span></dd>
                                    </div>
                                </dl>

                                <a href="{{ route('profile.checkout') }}" class="btn btn-primary mt-5 w-full">
                                    ادامه و ثبت سفارش
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7" /></svg>
                                </a>

                                <form method="POST" action="{{ route('cart.clear') }}" class="mt-3">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full text-center text-xs font-semibold text-muted transition hover:text-accent">خالی کردن سبد خرید</button>
                                </form>
                            </div>
                        </aside>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').content;

        document.querySelectorAll('[data-cart-row]').forEach((row) => {
            const itemId = row.dataset.itemId;
            const input = row.querySelector('[data-qty-input]');

            const send = (qty) => {
                fetch(`/cart/items/${itemId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ quantity: qty }),
                }).finally(() => window.location.reload());
            };

            row.querySelector('[data-qty-increase]')?.addEventListener('click', () => send(Number(input.value) + 1));
            row.querySelector('[data-qty-decrease]')?.addEventListener('click', () => send(Math.max(0, Number(input.value) - 1)));
            input?.addEventListener('change', () => send(Math.max(1, Number(input.value) || 1)));

            row.querySelector('[data-remove-item]')?.addEventListener('click', () => {
                fetch(`/cart/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                }).finally(() => window.location.reload());
            });
        });
    });
</script>
@endpush
