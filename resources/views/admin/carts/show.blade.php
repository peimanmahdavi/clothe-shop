@extends('layouts.admin')

@section('title', 'سبد خرید مشتری | پنل مدیریت')
@section('breadcrumb', 'سبدهای خرید')
@section('page-title', 'مدیریت سبد خرید')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
        $subtotal = $cart->items->sum(fn ($i) => $i->unit_price * $i->quantity);
    @endphp

    <div class="mb-4 flex items-center gap-2">
        <a href="{{ route('admin.carts.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted hover:text-ink">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 6 6 10l4 4"/></svg>
            بازگشت به سبدها
        </a>
    </div>

    @if (session('status'))
        <div class="mb-4 rounded-xl border border-green-100 bg-green-50 px-4 py-3 text-sm font-medium text-green-700" role="status">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-100 bg-accent-soft px-4 py-3 text-sm font-medium text-accent" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="mb-4 admin-card p-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-lg font-extrabold text-ink">{{ $cart->user?->getFullNameAttribute() ?? 'مهمان' }}</p>
                <p class="mt-0.5 text-sm text-muted" dir="ltr">{{ $cart->user?->phone ?? $cart->session_id }}</p>
            </div>
            <div class="text-end">
                <p class="text-xs text-muted">جمع سبد</p>
                <p class="text-lg font-extrabold text-brand-deep">{{ $toman($subtotal) }} <span class="text-[11px] font-medium text-muted">تومان</span></p>
            </div>
        </div>
    </div>

    <div class="mb-4 admin-card p-5">
        <h2 class="mb-3 text-sm font-extrabold text-ink">افزودن کالا به سبد</h2>
        <form method="POST" action="{{ route('admin.carts.items.add', $cart) }}" class="grid gap-3 sm:grid-cols-12">
            @csrf
            <div class="sm:col-span-5">
                <select name="product_id" class="field" required>
                    <option value="">انتخاب محصول…</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} — {{ $toPersian(number_format($product->finalPrice())) }} تومان (موجودی: {{ $toPersian($product->stock) }})</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-3">
                <input type="text" name="size" class="field" placeholder="سایز (اختیاری)" maxlength="50">
            </div>
            <div class="sm:col-span-2">
                <input type="text" name="color" class="field" placeholder="رنگ (اختیاری)" maxlength="50">
            </div>
            <div class="sm:col-span-1">
                <input type="number" name="quantity" value="1" min="1" max="99" class="field text-center" required>
            </div>
            <div class="sm:col-span-1">
                <button type="submit" class="btn btn-primary btn-sm w-full">افزودن</button>
            </div>
        </form>
    </div>

    <div class="admin-card overflow-hidden">
        <div class="flex items-center justify-between border-b border-line p-4">
            <h2 class="text-sm font-extrabold text-ink">اقلام سبد</h2>
            @if ($cart->items->isNotEmpty())
                <form method="POST" action="{{ route('admin.carts.clear', $cart) }}" onsubmit="return confirm('سبد مشتری خالی شود؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-xs font-bold text-accent hover:text-accent-deep">خالی کردن سبد</button>
                </form>
            @endif
        </div>

        @if ($cart->items->isEmpty())
            <div class="p-10 text-center text-sm text-muted">این سبد خالی است.</div>
        @else
            <div class="divide-y divide-line">
                @foreach ($cart->items as $item)
                    @php $product = $item->product; @endphp
                    <div class="flex flex-wrap items-center gap-3 p-4">
                        <div class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-brand-soft text-brand-deep">
                            @if ($product && $product->images->isNotEmpty())
                                <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @else
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true"><path d="M8 4 4.5 6 3 10l3 1.5V20h12v-8.5L21 10l-1.5-4L16 4a4 4 0 0 1-8 0Z"/></svg>
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-ink">{{ $product?->name ?? 'نامشخص' }}</p>
                            <div class="mt-1 flex flex-wrap gap-2 text-[11px] text-muted">
                                @if ($item->size)<span class="rounded-lg bg-canvas px-2 py-0.5">سایز: <span class="font-bold text-ink">{{ $item->size }}</span></span>@endif
                                @if ($item->color)<span class="rounded-lg bg-canvas px-2 py-0.5">رنگ: <span class="font-bold text-ink">{{ $item->color }}</span></span>@endif
                            </div>
                        </div>

                        <form method="POST" action="{{ route('admin.carts.items.update', [$cart, $item->id]) }}" class="flex items-center gap-2">
                            @csrf
                            @method('PUT')
                            <input type="number" name="quantity" value="{{ $item->quantity }}" min="0" max="99" class="field w-20 text-center" required>
                            <button type="submit" class="btn btn-outline btn-sm">ذخیره</button>
                        </form>

                        <p class="w-28 shrink-0 text-end text-sm font-extrabold text-ink">{{ $toman($item->unit_price * $item->quantity) }} <span class="text-[10px] font-medium text-muted">تومان</span></p>

                        <form method="POST" action="{{ route('admin.carts.items.remove', [$cart, $item->id]) }}" onsubmit="return confirm('این کالا حذف شود؟')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="icon-button text-red-600 hover:bg-red-50" aria-label="حذف">
                                <svg class="size-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
