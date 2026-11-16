@extends('layouts.app')

@section('title', 'ثبت سفارش | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $needsName = blank($user->first_name) || blank($user->last_name);
    @endphp

    <section class="py-8">
        <div class="container-page">
            <div class="mx-auto max-w-3xl">
                <div class="mb-8">
                    <h1 class="text-2xl font-extrabold text-ink">ثبت سفارش</h1>
                    <p class="mt-1 text-sm text-muted">آدرس تحویل را انتخاب کنید و اطلاعات خود را تکمیل نمایید</p>
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

                <form method="POST" action="{{ route('profile.orders.store') }}" class="space-y-6">
                    @csrf

                    @if ($product)
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="size" value="{{ $selectedSize }}">
                        <input type="hidden" name="color" value="{{ $selectedColor }}">

                        <div class="admin-card p-5">
                            <h2 class="mb-4 text-lg font-extrabold text-ink">محصول انتخابی</h2>
                            <div class="flex items-center gap-4">
                                <div class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-soft text-brand-deep">
                                    @if ($product->images->isNotEmpty())
                                        <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" /></svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold text-ink">{{ $product->name }}</p>
                                    <div class="mt-1 flex flex-wrap gap-2 text-xs text-muted">
                                        @if ($selectedSize)
                                            <span class="rounded-lg bg-canvas px-2 py-0.5">سایز: <span class="font-bold text-ink">{{ $selectedSize }}</span></span>
                                        @endif
                                        @if ($selectedColor)
                                            <span class="rounded-lg bg-canvas px-2 py-0.5">رنگ: <span class="font-bold text-ink">{{ $selectedColor }}</span></span>
                                        @endif
                                    </div>
                                </div>
                                <div class="shrink-0 text-end">
                                    <p class="text-sm font-extrabold text-ink">{{ $toPersian(number_format($product->finalPrice())) }} <span class="text-[10px] font-medium text-muted">تومان</span></p>
                                </div>
                            </div>
                        </div>
                    @endif

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

                    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-between">
                        <a href="{{ route('profile.dashboard') }}" class="btn btn-outline">انصراف</a>
                        <button type="submit" class="btn btn-primary" @disabled($addresses->isEmpty())>
                            <span class="btn-text">ثبت سفارش</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection
