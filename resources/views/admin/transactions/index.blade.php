@extends('layouts.admin')

@section('title', 'تراکنش‌ها | پنل مدیریت')
@section('breadcrumb', 'تراکنش‌ها')
@section('page-title', 'مدیریت تراکنش‌ها')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
            ',' => '٬',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));

        $kpis = [
            ['label' => 'مجموع تراکنش‌های موفق', 'value' => $toman($kpi['revenue']), 'unit' => 'تومان', 'hint' => 'درآمد تأییدشده', 'icon' => 'wallet', 'tone' => 'brand'],
            ['label' => 'تراکنش‌های امروز', 'value' => $toPersian($kpi['today']), 'unit' => 'تراکنش', 'hint' => $toman($kpi['today_revenue']).' تومان موفق', 'icon' => 'calendar', 'tone' => 'brand'],
            ['label' => 'ناموفق / در انتظار', 'value' => $toPersian($kpi['failed_pending']), 'unit' => 'تراکنش', 'hint' => 'نیازمند بررسی', 'icon' => 'alert', 'tone' => 'accent'],
            ['label' => 'مبالغ برگشتی', 'value' => $toman($kpi['refunded']), 'unit' => 'تومان', 'hint' => 'مسترد شده', 'icon' => 'refund', 'tone' => 'muted'],
        ];
    @endphp

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($kpis as $card)
            <div class="stat-card">
                <span class="pointer-events-none absolute -end-8 -top-8 size-24 rounded-full {{ $card['tone'] === 'accent' ? 'bg-accent-soft' : 'bg-brand-soft/70' }}" aria-hidden="true"></span>
                <div class="relative flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-muted">{{ $card['label'] }}</p>
                        <p class="mt-2 truncate text-lg font-extrabold text-ink sm:text-xl">
                            {{ $card['value'] }}
                            <span class="text-[11px] font-medium text-muted">{{ $card['unit'] }}</span>
                        </p>
                    </div>
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl {{ $card['tone'] === 'accent' ? 'bg-accent-soft text-accent' : 'bg-brand-soft text-brand-deep' }}">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            @switch($card['icon'])
                                @case('wallet')
                                    <path d="M3.5 8.5A2.5 2.5 0 0 1 6 6h12a2.5 2.5 0 0 1 2.5 2.5v7A2.5 2.5 0 0 1 18 18H6a2.5 2.5 0 0 1-2.5-2.5v-7Z" />
                                    <path d="M3.5 10.5h17" />
                                    <circle cx="16.5" cy="14.5" r="1.2" />
                                    @break
                                @case('calendar')
                                    <rect x="3.5" y="5" width="17" height="15" rx="2.5" />
                                    <path d="M8 3.5v3M16 3.5v3M3.5 10h17" />
                                    @break
                                @case('alert')
                                    <path d="M12 8v5M12 17h.01" />
                                    <path d="M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0Z" />
                                    @break
                                @default
                                    <path d="M3 9.5 12 4l9 5.5" />
                                    <path d="M5 10v9h14v-9" />
                                    <path d="M9 19v-5h6v5" />
                            @endswitch
                        </svg>
                    </span>
                </div>
                <p class="relative mt-3 text-[11px] font-medium text-muted">{{ $card['hint'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="admin-card mt-4 p-4">
        <form method="GET" action="{{ route('admin.transactions.index') }}" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
            <div class="xl:col-span-2">
                <label class="mb-1.5 block text-[11px] font-bold text-ink">جست‌وجو</label>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="field" placeholder="کد پیگیری، شماره سفارش، نام یا موبایل">
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-ink">وضعیت</label>
                <select name="status" class="field">
                    <option value="">همه وضعیت‌ها</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-ink">درگاه پرداخت</label>
                <select name="gateway" class="field">
                    <option value="">همه درگاه‌ها</option>
                    @foreach ($gateways as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['gateway'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-ink">از تاریخ</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="field" dir="ltr">
            </div>
            <div>
                <label class="mb-1.5 block text-[11px] font-bold text-ink">تا تاریخ</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="field" dir="ltr">
            </div>
            <div class="flex items-end gap-2 md:col-span-2 xl:col-span-6">
                <button type="submit" class="btn btn-primary btn-sm">اعمال فیلتر</button>
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline btn-sm">حذف فیلترها</a>
            </div>
        </form>
    </div>

    <div class="admin-card mt-4 overflow-hidden">
        @if ($transactions->isEmpty())
            <div class="p-10 text-center text-sm text-muted">تراکنشی یافت نشد.</div>
        @else
            <div class="hidden overflow-x-auto md:block">
                <table class="w-full min-w-[960px] text-sm">
                    <thead class="bg-canvas/70 text-[11px] text-muted">
                        <tr>
                            <th class="px-4 py-3 text-start font-bold">شناسه</th>
                            <th class="px-3 py-3 text-start font-bold">سفارش</th>
                            <th class="px-3 py-3 text-start font-bold">مشتری</th>
                            <th class="px-3 py-3 text-start font-bold">مبلغ</th>
                            <th class="px-3 py-3 text-start font-bold">درگاه</th>
                            <th class="px-3 py-3 text-start font-bold">کد پیگیری</th>
                            <th class="px-3 py-3 text-start font-bold">وضعیت</th>
                            <th class="px-3 py-3 text-start font-bold">تاریخ</th>
                            <th class="px-4 py-3 text-end font-bold">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($transactions as $trx)
                            @php
                                $customer = $trx->order?->customer_name ?? $trx->user?->name ?? 'مهمان';
                                $phone = $trx->order?->phone ?? $trx->user?->phone;
                                $payload = $trx->only(['id','amount','gateway','ref_id','tracking_code','card_number','status','description','admin_note','payment_method']) + [
                                    'gateway_label' => $trx->paymentMethodLabel(),
                                    'status_label' => $trx->statusLabel(),
                                    'date' => \App\Support\Jalali::format($trx->paid_at ?? $trx->created_at),
                                    'customer' => $customer,
                                    'phone' => $phone,
                                    'order' => $trx->order?->number,
                                    'receipt' => $trx->receiptUrl(),
                                    'update_url' => route('admin.transactions.update', $trx),
                                ];
                            @endphp
                            <tr class="cursor-pointer transition-colors hover:bg-canvas/60" data-trx-row data-trx='@json($payload)'>
                                <td class="whitespace-nowrap px-4 py-3 font-mono font-bold text-ink" dir="ltr">#{{ $trx->id }}</td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    @if ($trx->order)
                                        <a href="{{ route('admin.orders.show', $trx->order) }}" class="font-mono font-bold text-brand-deep hover:underline" dir="ltr">#{{ $trx->order->number }}</a>
                                    @else
                                        <span class="text-[11px] text-muted">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <p class="font-semibold text-ink">{{ $customer }}</p>
                                    @if ($phone)<p class="mt-0.5 text-[11px] text-muted" dir="ltr">{{ $phone }}</p>@endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 font-bold text-ink">
                                    {{ $toman($trx->amount) }} <span class="text-[10px] font-medium text-muted">تومان</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    <span class="badge-soft">{{ $trx->paymentMethodLabel() }}</span>
                                    @if ($trx->hasReceipt())
                                        <span class="ms-1 inline-flex align-middle text-brand-deep" title="فیش پیوست">
                                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3.5h12a1 1 0 0 1 1 1V20l-2.5-1.5L14 20l-2-1.5L10 20l-2.5-1.5L5 20V4.5a1 1 0 0 1 1-1Z"/></svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-3">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono text-xs font-bold text-ink" dir="ltr">{{ $trx->tracking_code ?? '—' }}</span>
                                        @if ($trx->tracking_code)
                                            <button type="button" class="icon-button size-7" data-copy="{{ $trx->tracking_code }}" aria-label="کپی کد پیگیری">
                                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h8"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <x-status-badge :status="$trx->status" :label="$trx->statusLabel()" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-3 text-[11px] text-muted">{{ $toPersian(\App\Support\Jalali::format($trx->paid_at ?? $trx->created_at, 'Y/m/d H:i')) }}</td>
                                <td class="px-4 py-3 text-end">
                                    <button type="button" class="rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">جزئیات</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="divide-y divide-line md:hidden">
                @foreach ($transactions as $trx)
                    @php
                        $customer = $trx->order?->customer_name ?? $trx->user?->name ?? 'مهمان';
                        $payload = $trx->only(['id','amount','gateway','ref_id','tracking_code','card_number','status','description','admin_note','payment_method']) + [
                            'gateway_label' => $trx->paymentMethodLabel(),
                            'status_label' => $trx->statusLabel(),
                            'date' => \App\Support\Jalali::format($trx->paid_at ?? $trx->created_at),
                            'customer' => $customer,
                            'phone' => $trx->order?->phone ?? $trx->user?->phone,
                            'order' => $trx->order?->number,
                            'receipt' => $trx->receiptUrl(),
                            'update_url' => route('admin.transactions.update', $trx),
                        ];
                    @endphp
                    <div class="flex cursor-pointer items-center gap-3 p-3 transition-colors hover:bg-canvas/60" data-trx-row data-trx='@json($payload)'>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-xs font-bold text-ink" dir="ltr">#{{ $trx->id }}</span>
                                <x-status-badge :status="$trx->status" :label="$trx->statusLabel()" />
                            </div>
                            <p class="mt-1 truncate text-sm font-semibold text-ink">{{ $customer }}</p>
                            <div class="mt-1 flex items-center gap-1 text-xs font-bold text-ink">
                                {{ $toman($trx->amount) }} <span class="text-[10px] font-medium text-muted">تومان</span>
                                @if ($trx->hasReceipt())
                                    <svg class="size-3.5 text-brand-deep" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-label="فیش پیوست"><path d="M6 3.5h12a1 1 0 0 1 1 1V20l-2.5-1.5L14 20l-2-1.5L10 20l-2.5-1.5L5 20V4.5a1 1 0 0 1 1-1Z"/></svg>
                                @endif
                            </div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-line bg-white px-2.5 py-2 text-[11px] font-bold text-ink">بررسی</span>
                    </div>
                @endforeach
            </div>

            <div class="border-t border-line p-4">{{ $transactions->links() }}</div>
        @endif
    </div>

    <div class="fixed inset-0 z-[60] hidden" data-trx-drawer aria-hidden="true">
        <div class="absolute inset-0 bg-ink/40 backdrop-blur-sm" data-trx-close></div>
        <aside class="absolute inset-y-0 start-0 flex w-full max-w-md flex-col border-e border-line bg-white shadow-2xl" role="dialog" aria-modal="true">
            <div class="flex items-center justify-between border-b border-line px-5 py-4">
                <div>
                    <p class="text-[11px] text-muted">جزئیات تراکنش</p>
                    <p class="font-mono text-sm font-extrabold text-ink" dir="ltr" data-trx-title>#—</p>
                </div>
                <button type="button" class="icon-button" data-trx-close aria-label="بستن">
                    <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>
            <div class="flex-1 space-y-4 overflow-y-auto p-5">
                <dl class="grid grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-[11px] text-muted">مبلغ</dt><dd class="font-bold text-ink" data-trx-amount>—</dd></div>
                    <div><dt class="text-[11px] text-muted">وضعیت</dt><dd data-trx-status>—</dd></div>
                    <div><dt class="text-[11px] text-muted">درگاه</dt><dd class="text-ink" data-trx-gateway>—</dd></div>
                    <div><dt class="text-[11px] text-muted">سفارش</dt><dd class="font-mono text-ink" dir="ltr" data-trx-order>—</dd></div>
                    <div><dt class="text-[11px] text-muted">کد پیگیری</dt><dd class="font-mono text-ink" dir="ltr" data-trx-tracking>—</dd></div>
                    <div><dt class="text-[11px] text-muted">شماره کارت</dt><dd class="font-mono text-ink" dir="ltr" data-trx-card>—</dd></div>
                    <div><dt class="text-[11px] text-muted">Ref ID</dt><dd class="font-mono text-ink" dir="ltr" data-trx-ref>—</dd></div>
                    <div><dt class="text-[11px] text-muted">تاریخ</dt><dd class="text-ink" data-trx-date>—</dd></div>
                    <div class="col-span-2"><dt class="text-[11px] text-muted">مشتری</dt><dd class="text-ink" data-trx-customer>—</dd></div>
                    <div class="col-span-2"><dt class="text-[11px] text-muted">توضیحات</dt><dd class="text-ink" data-trx-desc>—</dd></div>
                </dl>

                <div class="hidden" data-trx-receipt-wrap>
                    <p class="mb-2 text-[11px] text-muted">تصویر فیش واریزی</p>
                    <button type="button" class="block w-full overflow-hidden rounded-xl border border-line" data-lightbox="">
                        <img src="" alt="فیش واریزی" class="max-h-72 w-full object-contain" data-trx-receipt-img>
                    </button>
                    <p class="mt-1 text-[10px] text-muted">برای بزرگ‌نمایی کلیک کنید.</p>
                </div>

                <form method="POST" action="#" data-trx-form class="space-y-3 border-t border-line pt-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-ink">به‌روزرسانی وضعیت</label>
                        <select name="status" class="field" data-trx-status-select>
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-bold text-ink">توضیحات</label>
                        <textarea name="description" rows="3" class="field" data-trx-desc-input></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-full">ذخیره تغییرات</button>
                </form>
            </div>
        </aside>
    </div>

    <div class="fixed inset-0 z-[80] hidden items-center justify-center bg-ink/80 p-4" data-lightbox-overlay>
        <button type="button" class="absolute inset-0 cursor-zoom-out" data-lightbox-close aria-label="بستن"></button>
        <img src="" alt="فیش واریزی" class="relative max-h-full max-w-full rounded-xl shadow-2xl" data-lightbox-img>
    </div>
@endsection
