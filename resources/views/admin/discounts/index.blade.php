@extends('layouts.admin')

@section('title', 'تخفیف‌ها و جشنواره‌ها | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'تخفیف‌ها و جشنواره‌ها')

@section('page-title', 'مدیریت جشنواره‌ها')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);

        $relative = static function (?\Illuminate\Support\Carbon $date) use ($toPersian): string {
            if ($date === null) {
                return 'بدون محدودیت';
            }

            $days = (int) round(now()->startOfDay()->diffInDays($date->copy()->startOfDay(), false));

            if ($days === 0) {
                return 'امروز';
            }

            return $days > 0 ? $toPersian($days).' روز آینده' : $toPersian(abs($days)).' روز پیش';
        };
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">تخفیف‌ها و جشنواره‌ها</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">{{ $toPersian($discounts->count()) }} کمپین ثبت شده است.</p>
        </div>
    </div>

    <div class="mt-4 grid items-start gap-4 lg:grid-cols-5">
        <section class="admin-card overflow-hidden lg:col-span-3">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-sm">
                    <thead class="bg-canvas/70 text-[11px] text-muted">
                        <tr>
                            <th class="px-5 py-3 text-start font-bold">جشنواره</th>
                            <th class="px-3 py-3 text-start font-bold">تخفیف</th>
                            <th class="px-3 py-3 text-start font-bold">زمان</th>
                            <th class="px-3 py-3 text-start font-bold">وضعیت</th>
                            <th class="px-5 py-3 text-end font-bold">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($discounts as $discount)
                            <tr class="transition-colors hover:bg-canvas/60">
                                <td class="px-5 py-3">
                                    <p class="font-bold text-ink">{{ $discount->title }}</p>
                                    @if ($discount->code)
                                        <p class="mt-0.5 font-mono text-[11px] text-muted" dir="ltr">{{ $discount->code }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3">
                                    <span class="badge-sale">{{ $toPersian($discount->percentage) }}٪</span>
                                </td>
                                <td class="px-3 py-3 text-[11px] leading-5 text-muted">
                                    <span class="block">شروع: {{ $relative($discount->starts_at) }}</span>
                                    <span class="block">پایان: {{ $relative($discount->ends_at) }}</span>
                                </td>
                                <td class="px-3 py-3">
                                    @if (! $discount->is_active)
                                        <span class="status-pill bg-canvas text-muted">غیرفعال</span>
                                    @elseif ($discount->isExpired())
                                        <span class="status-pill bg-accent-soft text-accent">پایان یافته</span>
                                    @else
                                        <span class="status-pill bg-brand-soft text-brand-deep">در حال اجرا</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.discounts.edit', $discount) }}" class="rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">ویرایش</a>
                                        <form method="POST" action="{{ route('admin.discounts.destroy', $discount) }}" onsubmit="return confirm('جشنواره «{{ $discount->title }}» حذف شود؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-100 bg-accent-soft px-2.5 py-1.5 text-[11px] font-bold text-accent transition hover:border-accent hover:bg-accent hover:text-white">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-xs text-muted">هنوز جشنواره‌ای ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card p-5 lg:col-span-2">
            <h2 class="text-sm font-extrabold text-ink">جشنواره جدید</h2>
            <p class="mt-1 text-[11px] text-muted">جشنواره فعال در فروشگاه و شمارش معکوس نمایش داده می‌شود.</p>

            <form method="POST" action="{{ route('admin.discounts.store') }}" class="mt-4 space-y-3">
                @csrf

                <div>
                    <label for="title" class="mb-1.5 block text-xs font-bold text-ink">عنوان جشنواره</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" class="field" placeholder="مثلاً حراج پایان فصل" required>
                    @error('title')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="percentage" class="mb-1.5 block text-xs font-bold text-ink">درصد تخفیف</label>
                        <input id="percentage" name="percentage" type="number" min="1" max="90" value="{{ old('percentage') }}" class="field" dir="ltr" placeholder="45" required>
                        @error('percentage')
                            <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="code" class="mb-1.5 block text-xs font-bold text-ink">کد تخفیف</label>
                        <input id="code" name="code" type="text" value="{{ old('code') }}" class="field font-mono" dir="ltr" placeholder="SKY25">
                        @error('code')
                            <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="starts_at" class="mb-1.5 block text-xs font-bold text-ink">شروع</label>
                        <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" class="field" dir="ltr">
                        @error('starts_at')
                            <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="ends_at" class="mb-1.5 block text-xs font-bold text-ink">پایان</label>
                        <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" class="field" dir="ltr">
                        @error('ends_at')
                            <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="description" class="mb-1.5 block text-xs font-bold text-ink">توضیحات</label>
                    <textarea id="description" name="description" rows="2" class="field">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                @include('admin.discounts._scope')

                <label class="flex items-center gap-2 text-xs font-semibold text-ink">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-line text-brand-strong focus:ring-brand" @checked(old('is_active', true))>
                    جشنواره فعال باشد
                </label>

                <button type="submit" class="btn btn-primary w-full">ایجاد جشنواره</button>
            </form>
        </section>
    </div>
@endsection
