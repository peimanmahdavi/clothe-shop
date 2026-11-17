@extends('layouts.admin')

@section('title', 'سبدهای خرید | پنل مدیریت')
@section('breadcrumb', 'سبدهای خرید')
@section('page-title', 'مدیریت سبدهای خرید')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
    @endphp

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700" role="status">{{ session('status') }}</div>
    @endif

    <div class="admin-card mb-4 p-4">
        <form method="GET" action="{{ route('admin.carts.index') }}" class="grid gap-3 sm:grid-cols-3">
            <div class="sm:col-span-2">
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="field" placeholder="جست‌وجو با نام یا موبایل مشتری">
            </div>
            <button type="submit" class="btn btn-primary">جست‌وجو</button>
        </form>
    </div>

    <div class="admin-card overflow-hidden">
        @if ($carts->isEmpty())
            <div class="p-10 text-center text-sm text-muted">سبد خریدی یافت نشد.</div>
        @else
            {{-- Mobile cards --}}
            <div class="divide-y divide-line md:hidden">
                @foreach ($carts as $cart)
                    <div class="flex items-center gap-3 p-3">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-ink">{{ $cart->user?->getFullNameAttribute() ?? 'مهمان' }}</p>
                            <p class="mt-0.5 text-xs text-muted" dir="ltr">{{ $cart->user?->phone ?? $cart->session_id }}</p>
                            <p class="mt-1 text-xs font-bold text-ink">{{ $toPersian($cart->items_count) }} قلم — {{ $toman($cart->items->sum(fn ($i) => $i->unit_price * $i->quantity)) }} تومان</p>
                        </div>
                        <a href="{{ route('admin.carts.show', $cart) }}" class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-line bg-white px-2.5 py-2 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">مدیریت</a>
                    </div>
                @endforeach
            </div>

            {{-- Desktop table --}}
            <div class="hidden md:block">
                <table class="w-full text-sm">
                    <thead class="bg-canvas/70 text-[11px] text-muted">
                        <tr>
                            <th class="px-4 py-3 text-start font-bold">مشتری</th>
                            <th class="px-3 py-3 text-start font-bold">موبایل</th>
                            <th class="px-3 py-3 text-start font-bold">تعداد اقلام</th>
                            <th class="px-3 py-3 text-start font-bold">جمع سبد</th>
                            <th class="px-3 py-3 text-start font-bold">آخرین تغییر</th>
                            <th class="px-4 py-3 text-end font-bold">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($carts as $cart)
                            <tr class="transition-colors hover:bg-canvas/60">
                                <td class="px-4 py-3 font-semibold text-ink">{{ $cart->user?->getFullNameAttribute() ?? 'مهمان' }}</td>
                                <td class="px-3 py-3 text-xs text-muted" dir="ltr">{{ $cart->user?->phone ?? '—' }}</td>
                                <td class="px-3 py-3 text-ink">{{ $toPersian($cart->items_count) }}</td>
                                <td class="px-3 py-3 font-bold text-ink">{{ $toman($cart->items->sum(fn ($i) => $i->unit_price * $i->quantity)) }} تومان</td>
                                <td class="px-3 py-3 text-xs text-muted">{{ $toPersian($cart->updated_at->format('Y/m/d H:i')) }}</td>
                                <td class="px-4 py-3 text-end">
                                    <a href="{{ route('admin.carts.show', $cart) }}" class="inline-flex items-center gap-1 rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">مدیریت</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="border-t border-line p-4">{{ $carts->links() }}</div>
        @endif
    </div>
@endsection
