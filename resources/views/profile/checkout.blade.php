@extends('layouts.app')

@section('title', 'ثبت سفارش | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
        $needsName = blank($user->first_name) || blank($user->last_name);
    @endphp

    <section class="py-8">
        <div class="container-page">
            <div class="mx-auto max-w-5xl">
                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-ink">ثبت سفارش</h1>
                    <p class="mt-1 text-sm text-muted">آدرس تحویل، کد تخفیف و روش پرداخت را انتخاب کنید</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="alert">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.orders.store') }}" class="grid gap-6 lg:grid-cols-[1fr_20rem]">
                    @csrf

                    <div class="space-y-6">
                        {{-- Order items --}}
                        <div class="admin-card overflow-hidden">
                            <div class="flex items-center justify-between border-b border-line p-5">
                                <h2 class="text-lg font-extrabold text-ink">اقلام سفارش</h2>
                                <a href="{{ route('cart.index') }}" class="text-sm font-bold text-brand hover:underline">ویرایش سبد</a>
                            </div>
                            <div class="divide-y divide-line">
                                @foreach ($items as $item)
                                    @php $product = $item->product; @endphp
                                    <div class="flex items-center gap-4 p-4">
                                        <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-soft text-brand-deep">
                                            @if ($product && $product->images->isNotEmpty())
                                                <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                            @else
                                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" /></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="line-clamp-1 font-bold text-ink">{{ $product?->name ?? 'نامشخص' }}</p>
                                            <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-muted">
                                                @if ($item->size)<span class="rounded-lg bg-canvas px-2 py-0.5">سایز: <span class="font-bold text-ink">{{ $item->size }}</span></span>@endif
                                                @if ($item->color)<span class="rounded-lg bg-canvas px-2 py-0.5">رنگ: <span class="font-bold text-ink">{{ $item->color }}</span></span>@endif
                                                <span class="rounded-lg bg-canvas px-2 py-0.5">تعداد: <span class="font-bold text-ink">{{ $toPersian($item->quantity) }}</span></span>
                                            </div>
                                        </div>
                                        <p class="shrink-0 text-sm font-extrabold text-ink">{{ $toman($item->lineTotal()) }} <span class="text-[10px] font-medium text-muted">تومان</span></p>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Address selection --}}
                        <div class="admin-card p-5">
                            <div class="mb-4 flex items-center justify-between">
                                <h2 class="text-lg font-extrabold text-ink">آدرس تحویل</h2>
                                <a href="{{ route('profile.addresses') }}" class="text-sm font-bold text-brand hover:underline">مدیریت آدرس‌ها</a>
                            </div>

                            @if ($addresses->isEmpty())
                                <div class="rounded-xl border border-dashed border-line bg-canvas px-4 py-6 text-center">
                                    <p class="text-sm font-medium text-ink">هنوز آدرسی ثبت نکرده‌اید.</p>
                                    <p class="mt-1 text-xs text-muted">برای ثبت سفارش ابتدا یک آدرس اضافه کنید.</p>
                                    <a href="{{ route('profile.addresses') }}" class="btn btn-primary btn-sm mt-4 inline-flex">افزودن آدرس جدید</a>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach ($addresses as $address)
                                        <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-line p-4 transition hover:border-brand has-[:checked]:border-brand has-[:checked]:bg-brand-soft/40">
                                            <input type="radio" name="address_id" value="{{ $address->id }}" class="mt-1 text-brand focus:ring-brand"
                                                @checked((int) old('address_id', $defaultAddress?->id) === $address->id) required>
                                            <span class="min-w-0 flex-1">
                                                <span class="flex items-center gap-2">
                                                    <span class="font-bold text-ink">{{ $address->title }}</span>
                                                    @if ($address->is_default)
                                                        <span class="status-pill bg-brand-soft text-brand-deep">پیش‌فرض</span>
                                                    @endif
                                                </span>
                                                <span class="mt-1 block text-sm text-muted">{{ $address->full_name }}</span>
                                                <span class="mt-0.5 block text-sm text-muted">{{ $address->full_address }}</span>
                                                <span class="mt-0.5 block text-xs text-muted" dir="ltr">{{ $address->phone }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        {{-- Buyer info --}}
                        <div class="admin-card p-5">
                            <h2 class="mb-4 text-lg font-extrabold text-ink">اطلاعات خریدار</h2>

                            @if ($needsName)
                                <p class="mb-4 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-700">
                                    برای تکمیل سفارش، لطفاً نام و نام خانوادگی خود را وارد کنید.
                                </p>
                            @endif

                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="first_name" class="mb-2 block text-sm font-semibold text-ink">نام <span class="text-accent">*</span></label>
                                    <input type="text" id="first_name" name="first_name" class="field"
                                        value="{{ old('first_name', $user->first_name) }}" placeholder="علی" maxlength="50" autocomplete="given-name"
                                        @if ($needsName) required @endif>
                                </div>
                                <div>
                                    <label for="last_name" class="mb-2 block text-sm font-semibold text-ink">نام خانوادگی <span class="text-accent">*</span></label>
                                    <input type="text" id="last_name" name="last_name" class="field"
                                        value="{{ old('last_name', $user->last_name) }}" placeholder="احمدی" maxlength="50" autocomplete="family-name"
                                        @if ($needsName) required @endif>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-2 text-sm text-muted">
                                <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92Z" />
                                </svg>
                                <span>شماره تماس: <span dir="ltr">{{ $user->phone }}</span></span>
                            </div>
                        </div>

                        {{-- Payment method --}}
                        <div class="admin-card p-5">
                            <h2 class="mb-4 text-lg font-extrabold text-ink">روش پرداخت</h2>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-line p-4 transition hover:border-brand has-[:checked]:border-brand has-[:checked]:bg-brand-soft/40">
                                    <input type="radio" name="payment_method" value="gateway" class="mt-1 text-brand focus:ring-brand" @checked(old('payment_method', 'gateway') === 'gateway')>
                                    <span>
                                        <span class="block font-bold text-ink">درگاه پرداخت اینترنتی</span>
                                        <span class="mt-0.5 block text-xs text-muted">پرداخت آنی و تأیید خودکار سفارش.</span>
                                    </span>
                                </label>
                                <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-line p-4 transition hover:border-brand has-[:checked]:border-brand has-[:checked]:bg-brand-soft/40">
                                    <input type="radio" name="payment_method" value="card_to_card" class="mt-1 text-brand focus:ring-brand" @checked(old('payment_method') === 'card_to_card')>
                                    <span>
                                        <span class="block font-bold text-ink">کارت به کارت</span>
                                        <span class="mt-0.5 block text-xs text-muted">پس از ثبت سفارش، فیش واریزی را بارگذاری کنید.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Summary + discount --}}
                    <aside class="lg:sticky lg:top-28 lg:self-start">
                        <div class="admin-card p-5">
                            <h2 class="text-lg font-extrabold text-ink">خلاصه پرداخت</h2>

                            <div class="mt-4">
                                <label for="discount_code" class="mb-2 block text-sm font-semibold text-ink">کد تخفیف</label>
                                <div class="flex gap-2">
                                    <input type="text" id="discount_code" name="discount_code" class="field" value="{{ old('discount_code', $discountCode) }}" placeholder="مثلاً WELCOME" autocomplete="off">
                                    <button type="button" id="apply-discount" class="btn btn-outline btn-sm shrink-0">اعمال</button>
                                </div>
                                <p id="discount-feedback" class="mt-2 text-xs font-medium {{ $discountAmount > 0 ? 'text-green-600' : 'text-muted' }}">{{ $discountMessage }}</p>
                            </div>

                            <dl class="mt-5 space-y-3 border-t border-line pt-4 text-sm">
                                <div class="flex items-center justify-between">
                                    <dt class="text-muted">جمع کالاها</dt>
                                    <dd class="font-bold text-ink" id="subtotal-value">{{ $toman($subtotal) }} تومان</dd>
                                </div>
                                <div class="flex items-center justify-between @if ($discountAmount <= 0) hidden @endif" id="discount-row">
                                    <dt class="text-muted">تخفیف</dt>
                                    <dd class="font-bold text-accent" id="discount-value">−{{ $toman($discountAmount) }} تومان</dd>
                                </div>
                                <div class="flex items-center justify-between border-t border-line pt-3">
                                    <dt class="font-bold text-ink">مبلغ قابل پرداخت</dt>
                                    <dd class="text-lg font-extrabold text-brand-deep" id="total-value">{{ $toman($total) }} <span class="text-[11px] font-medium text-muted">تومان</span></dd>
                                </div>
                            </dl>

                            <button type="submit" class="btn btn-primary mt-5 w-full" @disabled($addresses->isEmpty())>
                                <span class="btn-text">ثبت سفارش</span>
                            </button>
                            <a href="{{ route('cart.index') }}" class="mt-3 block text-center text-xs font-semibold text-muted transition hover:text-ink">بازگشت به سبد خرید</a>
                        </div>
                    </aside>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toFa = (s) => String(s).replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[Number(d)]);
        const toman = (n) => toFa(Number(n).toLocaleString('en-US'));
        const btn = document.getElementById('apply-discount');
        const input = document.getElementById('discount_code');
        const feedback = document.getElementById('discount-feedback');

        if (!btn) return;

        btn.addEventListener('click', function () {
            btn.disabled = true;
            feedback.textContent = 'در حال بررسی...';
            feedback.className = 'mt-2 text-xs font-medium text-muted';

            fetch('{{ route('profile.checkout.discount') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ code: input.value }),
            })
                .then(async (r) => ({ ok: r.ok, data: await r.json() }))
                .then(({ ok, data }) => {
                    feedback.textContent = data.message || '';
                    feedback.className = 'mt-2 text-xs font-medium ' + (ok ? 'text-green-600' : 'text-accent');

                    if (ok) {
                        document.getElementById('subtotal-value').textContent = toman(data.subtotal) + ' تومان';
                        document.getElementById('discount-value').textContent = '−' + toman(data.discount_amount) + ' تومان';
                        document.getElementById('discount-row').classList.remove('hidden');
                        document.getElementById('total-value').innerHTML = toman(data.total) + ' <span class="text-[11px] font-medium text-muted">تومان</span>';
                    }
                })
                .catch(() => {
                    feedback.textContent = 'خطای شبکه. دوباره تلاش کنید.';
                    feedback.className = 'mt-2 text-xs font-medium text-accent';
                })
                .finally(() => { btn.disabled = false; });
        });
    });
</script>
@endpush
