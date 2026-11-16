@extends('layouts.admin')

@section('title', 'سفارش ' . $order->number . ' | پنل مدیریت')
@section('breadcrumb', 'سفارش‌ها / جزئیات')
@section('page-title', 'جزئیات سفارش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $statusClasses = [
            'pending_transaction' => 'bg-amber-100 text-amber-700',
            'pending_verification' => 'bg-amber-100 text-amber-700',
            'pending' => 'bg-amber-100 text-amber-700',
            'processing' => 'bg-brand-soft text-brand-deep',
            'shipped' => 'bg-blue-100 text-blue-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
        ];
    @endphp

    <div class="mb-4 flex items-center justify-between gap-3">
        <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-muted hover:text-ink">→ بازگشت به سفارش‌ها</a>
        <span class="status-pill {{ $statusClasses[$order->status] ?? 'bg-canvas text-muted' }}">{{ $order->statusLabel() }}</span>
    </div>

    <div class="grid items-start gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="admin-card p-5">
                <h2 class="mb-3 text-sm font-extrabold text-ink">اطلاعات سفارش</h2>
                <dl class="grid gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-[11px] text-muted">شماره سفارش</dt><dd class="font-mono font-bold text-ink" dir="ltr">{{ $order->number }}</dd></div>
                    <div><dt class="text-[11px] text-muted">تاریخ ثبت</dt><dd class="font-medium text-ink">{{ $toPersian($order->placed_at?->format('Y/m/d H:i') ?? '') }}</dd></div>
                    <div><dt class="text-[11px] text-muted">مبلغ</dt><dd class="font-bold text-ink">{{ $toPersian(number_format($order->total)) }} تومان</dd></div>
                    <div><dt class="text-[11px] text-muted">کد رهگیری</dt><dd class="font-mono text-ink" dir="ltr">{{ $order->tracking_code ?: '—' }}</dd></div>
                </dl>
            </div>

            <div class="admin-card p-5">
                <h2 class="mb-3 text-sm font-extrabold text-ink">محصول</h2>
                @if ($order->product)
                    <div class="flex items-center gap-3">
                        <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-soft text-brand-deep">
                            @if ($order->product->images->isNotEmpty())
                                <img src="{{ $order->product->images->first()->url() }}" alt="{{ $order->product->name }}" class="h-full w-full object-cover">
                            @else
                                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" /></svg>
                            @endif
                        </div>
                        <div>
                            <p class="font-bold text-ink">{{ $order->product->name }}</p>
                            <div class="mt-1 flex flex-wrap gap-2 text-xs text-muted">
                                @if ($order->size)<span class="rounded-lg bg-canvas px-2 py-0.5">سایز: <span class="font-bold text-ink">{{ $order->size }}</span></span>@endif
                                @if ($order->color)<span class="rounded-lg bg-canvas px-2 py-0.5">رنگ: <span class="font-bold text-ink">{{ $order->color }}</span></span>@endif
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-muted">محصولی برای این سفارش ثبت نشده است.</p>
                @endif
            </div>

            <div class="admin-card p-5">
                <h2 class="mb-3 text-sm font-extrabold text-ink">آدرس تحویل</h2>
                @if ($order->address)
                    <p class="text-sm text-muted">{{ $order->address->full_name }}</p>
                    <p class="mt-0.5 text-sm text-muted">{{ $order->address->full_address }}</p>
                    <p class="mt-0.5 text-sm text-muted" dir="ltr">{{ $order->address->phone }}</p>
                @else
                    <p class="text-sm text-muted">آدرسی ثبت نشده است.</p>
                @endif
            </div>

            @if ($transaction)
                <div class="admin-card p-5">
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <h2 class="text-sm font-extrabold text-ink">تأیید تراکنش و فیش</h2>
                        <span class="status-pill {{ $transaction->status === 'verified' ? 'bg-green-100 text-green-700' : ($transaction->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') }}">{{ $transaction->statusLabel() }}</span>
                    </div>

                    <dl class="grid gap-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-[11px] text-muted">روش پرداخت</dt><dd class="font-medium text-ink">{{ $transaction->paymentMethodLabel() }}</dd></div>
                        <div><dt class="text-[11px] text-muted">مبلغ</dt><dd class="font-bold text-ink">{{ $toPersian(number_format($transaction->amount)) }} تومان</dd></div>
                        <div><dt class="text-[11px] text-muted">کد پیگیری / کارت</dt><dd class="font-mono text-ink" dir="ltr">{{ $transaction->tracking_code ?: '—' }}</dd></div>
                        <div><dt class="text-[11px] text-muted">زمان ارسال</dt><dd class="text-ink">{{ $toPersian(\App\Support\Jalali::format($transaction->updated_at, 'Y/m/d H:i')) }}</dd></div>
                    </dl>

                    @if ($transaction->hasReceipt())
                        <div class="mt-4">
                            <p class="mb-2 text-[11px] font-bold text-ink">تصویر فیش واریزی</p>
                            <button type="button" class="block w-full overflow-hidden rounded-xl border border-line" data-lightbox="{{ $transaction->receiptUrl() }}">
                                <img src="{{ $transaction->receiptUrl() }}" alt="فیش واریزی" class="max-h-72 w-full object-contain" loading="lazy">
                            </button>
                            <p class="mt-1 text-[10px] text-muted">برای بزرگ‌نمایی کلیک کنید.</p>
                        </div>
                    @else
                        <p class="mt-4 rounded-lg border border-dashed border-line bg-canvas px-3 py-4 text-center text-[11px] text-muted">هنوز فیشی بارگذاری نشده است.</p>
                    @endif

                    @if ($transaction->admin_note)
                        <p class="mt-3 rounded-lg bg-accent-soft px-3 py-2 text-[11px] font-medium text-accent">یادداشت مدیر: {{ $transaction->admin_note }}</p>
                    @endif

                    @if ($transaction->hasReceipt() && $transaction->status !== 'verified')
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            <form method="POST" action="{{ route('admin.orders.approve-transaction', $order) }}">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm w-full">تایید تراکنش و سفارش</button>
                            </form>
                            <form method="POST" action="{{ route('admin.orders.reject-transaction', $order) }}" class="space-y-2">
                                @csrf
                                <input type="text" name="admin_note" class="field" placeholder="دلیل رد فیش (اختیاری)">
                                <button type="submit" class="btn btn-accent btn-sm w-full">رد فیش</button>
                            </form>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="admin-card space-y-4 p-5">
            <h2 class="text-sm font-extrabold text-ink">تغییر وضعیت</h2>
            <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <div>
                    <label for="status" class="mb-1.5 block text-xs font-bold text-ink">وضعیت سفارش</label>
                    <select id="status" name="status" class="field">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="tracking_code" class="mb-1.5 block text-xs font-bold text-ink">کد رهگیری پستی</label>
                    <input id="tracking_code" name="tracking_code" type="text" value="{{ old('tracking_code', $order->tracking_code) }}" class="field" dir="ltr" placeholder="برای سفارش‌های ارسال‌شده">
                    @error('tracking_code')<p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>@enderror
                </div>
                <button type="submit" class="btn btn-primary w-full">ذخیره تغییرات</button>
            </form>
        </div>
    </div>

    <div class="fixed inset-0 z-[70] hidden items-center justify-center bg-ink/80 p-4" data-lightbox-overlay>
        <button type="button" class="absolute inset-0 cursor-zoom-out" data-lightbox-close aria-label="بستن"></button>
        <img src="" alt="فیش واریزی" class="relative max-h-full max-w-full rounded-xl shadow-2xl" data-lightbox-img>
    </div>
@endsection
