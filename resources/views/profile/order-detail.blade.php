@extends('layouts.app')

@section('title', 'سفارش #'.$order->number.' | آسمان‌پوش')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
        $jalali = static fn ($date): string => $date ? $toPersian(\App\Support\Jalali::format($date)) : '—';

        $isPendingTransaction = $order->status === 'pending_transaction';
        $isPendingVerification = $order->status === 'pending_verification';
        $wasRejected = $isPendingTransaction && $transaction && $transaction->status === 'rejected';

        $statusStyles = [
            'pending_transaction' => ['class' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200', 'icon' => 'clock'],
            'pending_verification' => ['class' => 'bg-sky-100 text-sky-700 ring-1 ring-sky-200', 'icon' => 'shield'],
            'pending' => ['class' => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200', 'icon' => 'clock'],
            'processing' => ['class' => 'bg-brand-soft text-brand-deep ring-1 ring-brand/30', 'icon' => 'cog'],
            'shipped' => ['class' => 'bg-blue-100 text-blue-700 ring-1 ring-blue-200', 'icon' => 'truck'],
            'completed' => ['class' => 'bg-green-100 text-green-700 ring-1 ring-green-200', 'icon' => 'check'],
            'cancelled' => ['class' => 'bg-red-100 text-red-700 ring-1 ring-red-200', 'icon' => 'x'],
        ];
        $badge = $statusStyles[$order->status] ?? ['class' => 'bg-canvas text-muted ring-1 ring-line', 'icon' => 'dot'];
    @endphp

    <section class="py-8">
        <div class="container-page">
            <div class="mx-auto max-w-3xl">

                @if (session('status'))
                    <div class="mb-6 flex items-center gap-3 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700" role="status">
                        <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7" /></svg>
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="alert">{{ $errors->first() }}</div>
                @endif

                {{-- Header --}}
                <div class="mb-6">
                    <a href="{{ route('profile.orders') }}" class="mb-3 inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 6 6 10l4 4" /></svg>
                        برگشت به سفارش‌ها
                    </a>
                    <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div class="min-w-0">
                            <h1 class="text-2xl font-extrabold text-ink">جزئیات سفارش</h1>
                            <p class="mt-1 text-sm text-muted">شماره سفارش: <span class="font-mono font-bold text-ink" dir="ltr">#{{ $order->number }}</span></p>
                        </div>
                        <span class="status-pill shrink-0 gap-1.5 px-3 py-1.5 text-xs {{ $badge['class'] }}">
                            @switch($badge['icon'])
                                @case('clock')
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" /></svg>
                                    @break
                                @case('shield')
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z" /><path d="m9 12 2 2 4-4" /></svg>
                                    @break
                                @case('truck')
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 3h13v13H1zM14 8h4l3 3v5h-7" /><circle cx="5.5" cy="18.5" r="1.8" /><circle cx="17.5" cy="18.5" r="1.8" /></svg>
                                    @break
                                @case('check')
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7" /></svg>
                                    @break
                                @case('x')
                                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                                    @break
                                @default
                                    <span class="size-2 rounded-full bg-current"></span>
                            @endswitch
                            {{ $order->statusLabel() }}
                        </span>
                    </div>
                </div>

                {{-- ===================== Card-to-Card Receipt Box ===================== --}}
                @if ($isPendingTransaction)
                    <div class="mb-6 overflow-hidden rounded-2xl border border-amber-200 bg-gradient-to-b from-amber-50 to-white shadow-sm">
                        <div class="flex items-center gap-2 border-b border-amber-200/70 bg-amber-100/60 px-5 py-3">
                            <svg class="size-5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 9v4M12 17h.01" /><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z" /></svg>
                            <h2 class="text-sm font-extrabold text-amber-800">ثبت فیش واریز کارت به کارت</h2>
                        </div>

                        <div class="space-y-5 p-5">
                            @if ($wasRejected && $transaction->admin_note)
                                <div class="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                    <svg class="mt-0.5 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M12 8v4M12 16h.01" /></svg>
                                    <span><span class="font-bold">فیش قبلی رد شد:</span> {{ $transaction->admin_note }}</span>
                                </div>
                            @endif

                            {{-- Amount + destination card --}}
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-xl border border-line bg-white p-4">
                                    <p class="text-[11px] font-medium text-muted">مبلغ قابل پرداخت</p>
                                    <p class="mt-1 text-xl font-extrabold text-ink">{{ $toman($order->total) }} <span class="text-xs font-medium text-muted">تومان</span></p>
                                </div>
                                <div class="rounded-xl border border-line bg-white p-4">
                                    <p class="text-[11px] font-medium text-muted">شماره کارت مقصد</p>
                                    <div class="mt-1 flex items-center justify-between gap-2">
                                        <span class="font-mono text-base font-extrabold tracking-wide text-brand-deep" dir="ltr">{{ \App\Models\Transaction::destinationCard() }}</span>
                                        <button type="button" class="inline-flex items-center gap-1 rounded-lg border border-brand/40 bg-brand-soft px-2.5 py-1.5 text-[11px] font-bold text-brand-deep transition hover:bg-brand-strong hover:text-white" data-copy="{{ \App\Models\Transaction::destinationCard() }}">
                                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="11" height="11" rx="2" /><path d="M5 15V5a2 2 0 0 1 2-2h8" /></svg>
                                            کپی شماره کارت
                                        </button>
                                    </div>
                                    <p class="mt-1.5 text-[11px] text-muted">به نام {{ \App\Models\Transaction::destinationAccountName() }} — {{ \App\Models\Transaction::destinationBankName() }}</p>
                                </div>
                            </div>

                            {{-- Upload form --}}
                            <form method="POST" action="{{ route('profile.orders.receipt.store', $order) }}" enctype="multipart/form-data" class="space-y-4">
                                @csrf

                                <div>
                                    <label for="tracking_code" class="mb-1.5 block text-sm font-semibold text-ink">شماره پیگیری تراکنش یا ۴ رقم آخر کارت مبدا <span class="text-accent">*</span></label>
                                    <input type="text" id="tracking_code" name="tracking_code" class="field" dir="ltr"
                                        value="{{ old('tracking_code', $transaction?->tracking_code) }}" placeholder="مثلاً ۱۲۳۴" required>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-semibold text-ink">تصویر فیش واریزی <span class="text-accent">*</span></label>
                                    <input type="file" id="receipt_image" name="receipt_image" accept="image/jpeg,image/png,image/webp" class="sr-only" required data-receipt-input>

                                    <label for="receipt_image" data-receipt-dropzone class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-line bg-canvas/60 px-4 py-8 text-center transition hover:border-brand hover:bg-brand-soft/30">
                                        <span data-receipt-empty>
                                            <span class="mx-auto mb-2 flex size-12 items-center justify-center rounded-full bg-brand-soft text-brand-deep">
                                                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5" /><path d="M5 16v3h14v-3" /></svg>
                                            </span>
                                            <span class="block text-sm font-bold text-ink">تصویر فیش را بکشید و اینجا رها کنید</span>
                                            <span class="mt-1 block text-[11px] text-muted">یا برای انتخاب کلیک کنید — JPG / PNG / WEBP تا ۵ مگابایت</span>
                                        </span>
                                        <span data-receipt-preview class="hidden w-full">
                                            <img src="" alt="پیش‌نمایش فیش" class="mx-auto max-h-56 rounded-xl border border-line object-contain" data-receipt-img>
                                            <span class="mt-2 inline-flex items-center gap-1 text-xs font-bold text-brand-deep">
                                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" /><path d="M18.5 2.5a2.1 2.1 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z" /></svg>
                                                تغییر تصویر
                                            </span>
                                        </span>
                                    </label>
                                    <p class="mt-1 text-[11px] font-medium text-accent hidden" data-receipt-error></p>
                                </div>

                                <button type="submit" class="btn btn-primary w-full">
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7" /></svg>
                                    ارسال فیش و تایید پرداخت
                                </button>
                            </form>
                        </div>
                    </div>
                @elseif ($isPendingVerification && $transaction)
                    {{-- Submitted receipt preview --}}
                    <div class="mb-6 overflow-hidden rounded-2xl border border-sky-200 bg-gradient-to-b from-sky-50 to-white shadow-sm">
                        <div class="flex items-center gap-2 border-b border-sky-200/70 bg-sky-100/60 px-5 py-3">
                            <svg class="size-5 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6l7-3Z" /><path d="m9 12 2 2 4-4" /></svg>
                            <h2 class="text-sm font-extrabold text-sky-800">فیش شما ثبت شده است</h2>
                        </div>
                        <div class="flex flex-col gap-4 p-5 sm:flex-row sm:items-center">
                            @if ($transaction->receiptUrl())
                                <a href="{{ $transaction->receiptUrl() }}" target="_blank" rel="noopener" class="shrink-0">
                                    <img src="{{ $transaction->receiptUrl() }}" alt="فیش واریزی" class="h-24 w-24 rounded-xl border border-line object-cover">
                                </a>
                            @endif
                            <div class="min-w-0 flex-1 space-y-1 text-sm">
                                <p class="text-muted">کد پیگیری: <span class="font-mono font-bold text-ink" dir="ltr">{{ $transaction->tracking_code ?? '—' }}</span></p>
                                <p class="text-muted">زمان ارسال: <span class="font-medium text-ink">{{ $jalali($transaction->updated_at) }}</span></p>
                                <p class="mt-2 rounded-lg bg-sky-50 px-3 py-2 text-[12px] font-medium text-sky-700">فیش شما ثبت شده و در انتظار تایید مدیریت است.</p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Order Info Grid --}}
                <div class="mb-6 grid gap-4 lg:grid-cols-2">
                    {{-- Shipping Address --}}
                    <div class="admin-card p-5">
                        <h3 class="flex items-center gap-2 font-bold text-ink">
                            <svg class="size-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0Z" /><circle cx="12" cy="10" r="3" /></svg>
                            آدرس تحویل
                        </h3>
                        <div class="mt-3 space-y-1 text-sm">
                            <p class="font-medium text-ink">{{ $order->shipping_full_name }}</p>
                            <p class="text-muted" dir="ltr">{{ $order->shipping_phone }}</p>
                            @if ($order->address)
                                <p class="text-muted">{{ $order->address->full_address }}</p>
                                @if ($order->address->postal_code)
                                    <p class="text-muted">کد پستی: {{ $toPersian($order->address->postal_code) }}</p>
                                @endif
                            @else
                                <p class="text-muted">آدرس کامل در سیستم موجود نیست</p>
                            @endif
                        </div>
                    </div>

                    {{-- Order Summary --}}
                    <div class="admin-card p-5">
                        <h3 class="flex items-center gap-2 font-bold text-ink">
                            <svg class="size-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><polyline points="14 2 14 8 20 8" /><line x1="16" y1="13" x2="8" y2="13" /><line x1="16" y1="17" x2="8" y2="17" /></svg>
                            خلاصه سفارش
                        </h3>
                        <dl class="mt-3 space-y-3 text-sm">
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted">تاریخ ثبت</dt>
                                <dd class="font-medium text-ink">{{ $jalali($order->placed_at) }}</dd>
                            </div>
                            <div class="flex justify-between gap-3">
                                <dt class="text-muted">مجموع کالاها</dt>
                                <dd class="font-medium text-ink">{{ $toman($order->subtotal ?: $order->total) }} تومان</dd>
                            </div>
                            @if ($order->discount_amount > 0)
                                <div class="flex justify-between gap-3">
                                    <dt class="text-muted">تخفیف @if ($order->discount_code)<span class="font-mono text-[10px]">({{ $order->discount_code }})</span>@endif</dt>
                                    <dd class="font-bold text-accent">−{{ $toman($order->discount_amount) }} تومان</dd>
                                </div>
                            @endif
                            <div class="flex justify-between gap-3 border-t border-line pt-3">
                                <dt class="font-bold text-muted">مبلغ قابل پرداخت</dt>
                                <dd class="text-lg font-bold text-brand-deep">{{ $toman($order->total) }} تومان</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                {{-- Order Items --}}
                <div class="admin-card overflow-hidden">
                    <div class="border-b border-line p-5">
                        <h3 class="flex items-center gap-2 font-bold text-ink">
                            <svg class="size-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8h12l-1 12H7L6 8Z" /><path d="M9 8a3 3 0 0 1 6 0" /></svg>
                            اقلام سفارش
                        </h3>
                    </div>
                    @if ($order->items->isNotEmpty())
                        <div class="divide-y divide-line">
                            @foreach ($order->items as $item)
                                @php $image = $item->product?->images->first(); @endphp
                                <div class="flex items-center gap-3 p-4">
                                    <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-line bg-brand-soft text-brand-deep">
                                        @if ($image)
                                            <img src="{{ $image->url() }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">
                                        @else
                                            <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z" /></svg>
                                        @endif
                                    </div>

                                    <div class="min-w-0 flex-1">
                                        <p class="font-bold text-ink">{{ $item->product_name }}</p>
                                        <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-muted">
                                            @if ($item->size)<span class="rounded-lg bg-canvas px-2 py-0.5">سایز: <span class="font-bold text-ink">{{ $item->size }}</span></span>@endif
                                            @if ($item->color)<span class="rounded-lg bg-canvas px-2 py-0.5">رنگ: <span class="font-bold text-ink">{{ $item->color }}</span></span>@endif
                                            <span class="rounded-lg bg-canvas px-2 py-0.5">{{ $toPersian($item->quantity) }} × {{ $toman($item->unit_price) }} تومان</span>
                                        </div>
                                    </div>

                                    <p class="shrink-0 text-sm font-extrabold text-ink">{{ $toman($item->line_total) }} <span class="text-[10px] font-medium text-muted">تومان</span></p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-5 text-center text-sm text-muted">قلمی برای این سفارش ثبت نشده است.</div>
                    @endif
                </div>

                {{-- Status Timeline (RTL-safe flexbox) --}}
                <div class="mt-6 admin-card overflow-hidden">
                    <div class="border-b border-line p-5">
                        <h3 class="font-bold text-ink">زمان‌بندی وضعیت</h3>
                    </div>
                    <div class="p-5">
                        @php
                            $steps = [
                                ['label' => 'ثبت سفارش', 'time' => $order->placed_at, 'done' => true],
                                ['label' => 'ثبت فیش واریزی', 'time' => in_array($order->status, ['pending_verification', 'processing', 'shipped', 'completed']) ? $order->transactions->first()?->updated_at : null, 'done' => in_array($order->status, ['pending_verification', 'processing', 'shipped', 'completed'])],
                                ['label' => 'تایید پرداخت و پردازش', 'time' => in_array($order->status, ['processing', 'shipped', 'completed']) ? $order->updated_at : null, 'done' => in_array($order->status, ['processing', 'shipped', 'completed'])],
                                ['label' => 'ارسال شده', 'time' => in_array($order->status, ['shipped', 'completed']) ? $order->updated_at : null, 'done' => in_array($order->status, ['shipped', 'completed'])],
                                ['label' => 'تکمیل شده', 'time' => $order->status === 'completed' ? $order->updated_at : null, 'done' => $order->status === 'completed'],
                            ];
                            if ($order->status === 'cancelled') {
                                $steps[] = ['label' => 'لغو شده', 'time' => $order->updated_at, 'done' => true, 'cancelled' => true];
                            }
                            $lastIndex = count($steps) - 1;
                        @endphp

                        <ol class="space-y-0">
                            @foreach ($steps as $i => $step)
                                <li class="flex items-start gap-4">
                                    {{-- Right column: dot + connecting line --}}
                                    <div class="flex w-8 flex-col items-center">
                                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full
                                            @if (!empty($step['cancelled'])) bg-red-100 text-red-600 ring-2 ring-red-200
                                            @elseif ($step['done']) bg-brand-strong text-white ring-4 ring-brand-soft
                                            @else bg-white text-muted ring-2 ring-line @endif">
                                            @if (!empty($step['cancelled']))
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" /></svg>
                                            @elseif ($step['done'])
                                                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5" /></svg>
                                            @else
                                                <span class="size-2 rounded-full bg-line"></span>
                                            @endif
                                        </span>
                                        @if ($i !== $lastIndex)
                                            <span class="mt-1 w-0.5 flex-1 {{ $step['done'] ? 'bg-brand' : 'bg-slate-200' }}" aria-hidden="true"></span>
                                        @endif
                                    </div>

                                    {{-- Left column: text --}}
                                    <div class="min-w-0 flex-1 pb-6">
                                        <p class="pt-1 text-sm font-bold {{ $step['done'] ? 'text-ink' : 'text-muted' }}">{{ $step['label'] }}</p>
                                        <p class="mt-0.5 text-xs text-muted">{{ $jalali($step['time']) }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ---- Card number copy (uses global data-copy handler if present; fallback here) ----
        document.querySelectorAll('[data-copy]').forEach(function (btn) {
            btn.addEventListener('click', async function () {
                const value = btn.dataset.copy;
                try {
                    await navigator.clipboard.writeText(value);
                    const original = btn.innerHTML;
                    btn.innerHTML = '<svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="m5 13 4 4L19 7"/></svg> کپی شد';
                    setTimeout(function () { btn.innerHTML = original; }, 1800);
                } catch (e) {}
            });
        });

        // ---- Receipt image preview (drag & drop + file picker) ----
        const input = document.querySelector('[data-receipt-input]');
        const dropzone = document.querySelector('[data-receipt-dropzone]');
        const empty = document.querySelector('[data-receipt-empty]');
        const preview = document.querySelector('[data-receipt-preview]');
        const img = document.querySelector('[data-receipt-img]');
        const errorEl = document.querySelector('[data-receipt-error]');

        if (!input || !dropzone) return;

        const MAX = 5 * 1024 * 1024;
        const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

        function showFile(file) {
            errorEl.classList.add('hidden');
            if (!file) return;

            if (!ALLOWED.includes(file.type)) {
                errorEl.textContent = 'فقط تصویر JPG، PNG یا WEBP مجاز است.';
                errorEl.classList.remove('hidden');
                input.value = '';
                return;
            }
            if (file.size > MAX) {
                errorEl.textContent = 'حجم تصویر نباید بیشتر از ۵ مگابایت باشد.';
                errorEl.classList.remove('hidden');
                input.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                img.src = e.target.result;
                empty.classList.add('hidden');
                preview.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }

        input.addEventListener('change', function () { showFile(input.files[0]); });

        ['dragenter', 'dragover'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.add('border-brand', 'bg-brand-soft/40');
            });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            dropzone.addEventListener(evt, function (e) {
                e.preventDefault();
                dropzone.classList.remove('border-brand', 'bg-brand-soft/40');
            });
        });
        dropzone.addEventListener('drop', function (e) {
            const files = e.dataTransfer.files;
            if (files && files.length) {
                input.files = files;
                showFile(files[0]);
            }
        });
    });
</script>
@endpush
