@extends('layouts.app')

@section('title', 'بارگذاری فیش واریزی | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
            ',' => '٬',
        ]);
        $alreadyUploaded = $transaction?->hasReceipt();
    @endphp

    <section class="py-8">
        <div class="container-page">
            <div class="mx-auto max-w-2xl">
                <div class="mb-6">
                    <a href="{{ route('profile.orders') }}" class="text-sm font-medium text-muted hover:text-ink">→ بازگشت به سفارش‌ها</a>
                    <h1 class="mt-3 text-2xl font-extrabold text-ink">بارگذاری فیش واریزی</h1>
                    <p class="mt-1 text-sm text-muted">برای تکمیل سفارش، مبلغ را کارت به کارت کرده و تصویر فیش را بارگذاری کنید.</p>
                </div>

                @if (session('status'))
                    <div class="mb-5 rounded-xl border border-brand/30 bg-brand-soft px-4 py-3 text-sm font-bold text-brand-deep">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="mb-5 rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="alert">{{ $errors->first() }}</div>
                @endif

                <div class="admin-card mb-4 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-xs font-medium text-muted">شماره سفارش</span>
                        <span class="font-mono font-bold text-ink" dir="ltr">{{ $order->number }}</span>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3 border-t border-line pt-3">
                        <span class="text-xs font-medium text-muted">مبلغ قابل پرداخت</span>
                        <span class="text-lg font-extrabold text-ink">{{ $toPersian(number_format($order->total)) }} <span class="text-[11px] font-medium text-muted">تومان</span></span>
                    </div>
                </div>

                <div class="admin-card mb-4 p-5">
                    <p class="text-xs font-medium text-muted">شماره کارت مقصد</p>
                    <div class="mt-2 flex items-center justify-between gap-2 rounded-xl border border-dashed border-brand bg-brand-soft/40 px-4 py-3">
                        <span class="font-mono text-lg font-extrabold tracking-wide text-brand-deep" dir="ltr">{{ \App\Models\Transaction::destinationCard() }}</span>
                        <button type="button" class="btn btn-primary btn-sm" data-copy="{{ \App\Models\Transaction::destinationCard() }}">
                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h8"/></svg>
                            کپی
                        </button>
                    </div>
                    <p class="mt-2 text-[11px] text-muted">به نام {{ \App\Models\Transaction::destinationAccountName() }} — {{ \App\Models\Transaction::destinationBankName() }}</p>
                </div>

                @if ($alreadyUploaded)
                    <div class="admin-card mb-4 p-5">
                        <p class="mb-2 text-sm font-bold text-ink">فیش بارگذاری‌شده</p>
                        <a href="{{ $transaction->receiptUrl() }}" target="_blank" rel="noopener">
                            <img src="{{ $transaction->receiptUrl() }}" alt="فیش واریزی" class="max-h-64 w-full rounded-xl border border-line object-contain">
                        </a>
                        <p class="mt-2 text-[11px] text-muted">می‌توانید برای اصلاح، فیش جدید بارگذاری کنید.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.orders.receipt.store', $order) }}" enctype="multipart/form-data" class="admin-card space-y-4 p-5">
                    @csrf
                    <div>
                        <label for="tracking_code" class="mb-2 block text-sm font-semibold text-ink">شماره پیگیری / ۴ رقم آخر کارت <span class="text-accent">*</span></label>
                        <input type="text" id="tracking_code" name="tracking_code" class="field" dir="ltr"
                            value="{{ old('tracking_code', $transaction?->tracking_code) }}" placeholder="مثلاً ۱۲۳۴ یا کد پیگیری" required>
                    </div>
                    <div>
                        <label for="receipt_image" class="mb-2 block text-sm font-semibold text-ink">تصویر فیش واریزی <span class="text-accent">*</span></label>
                        <input type="file" id="receipt_image" name="receipt_image" accept="image/*" class="field" required>
                        <p class="mt-1 text-[11px] text-muted">فرمت JPG/PNG/WEBP تا حداکثر ۴ مگابایت.</p>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">ثبت و ارسال برای تأیید</button>
                </form>
            </div>
        </div>
    </section>
@endsection
