@extends('layouts.admin')

@section('title', 'محصولات | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'محصولات')

@section('page-title', 'مدیریت محصولات')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
            ',' => '٬',
        ]);

        $toman = static fn (int $amount): string => $toPersian(number_format($amount));
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">محصولات</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">{{ $toPersian($products->total()) }} محصول ثبت شده است.</p>
        </div>
        <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14" />
            </svg>
            افزودن محصول
        </a>
    </div>

    <form method="GET" action="{{ route('admin.products.index') }}" class="mt-4 flex flex-wrap items-center gap-2 rounded-2xl border border-line bg-white p-3 shadow-card">
        <div class="relative min-w-56 flex-1">
            <svg class="pointer-events-none absolute inset-y-0 start-3.5 my-auto size-4.5 text-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.5-3.5" />
            </svg>
            <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" class="search-input" placeholder="جست‌وجوی نام محصول">
        </div>

        <select name="category" class="field sm:w-52">
            <option value="">همه دسته‌بندی‌ها</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) ($filters['category'] ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-primary btn-sm">اعمال فیلتر</button>

        @if (filled($filters['search'] ?? null) || filled($filters['category'] ?? null))
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">حذف فیلتر</a>
        @endif
    </form>

    <div class="mt-4 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[860px] text-sm">
                <thead class="bg-canvas/70 text-[11px] text-muted">
                    <tr>
                        <th class="px-5 py-3 text-start font-bold">محصول</th>
                        <th class="px-3 py-3 text-start font-bold">دسته / برند</th>
                        <th class="px-3 py-3 text-start font-bold">قیمت</th>
                        <th class="px-3 py-3 text-start font-bold">موجودی</th>
                        <th class="px-3 py-3 text-start font-bold">وضعیت</th>
                        <th class="px-5 py-3 text-end font-bold">عملیات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($products as $product)
                        <tr class="transition-colors hover:bg-canvas/60">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex size-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-line bg-brand-soft/40">
                                        @if ($product->images->first())
                                            <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="size-12 object-cover" width="48" height="48" loading="lazy">
                                        @else
                                            <svg class="size-5 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <rect x="3.5" y="4.5" width="17" height="15" rx="2.5" />
                                                <path d="m6 16 4-4 3 3 2-2 3 3" />
                                            </svg>
                                        @endif
                                    </span>
                                    <div class="min-w-0 leading-tight">
                                        <p class="truncate font-bold text-ink">{{ $product->name }}</p>
                                        <p class="mt-0.5 text-[10px] text-muted">{{ $toPersian($product->images->count()) }} تصویر • {{ $toPersian($product->reviews_count) }} نظر</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3 text-[11px]">
                                <p class="font-semibold text-ink">{{ $product->category?->name ?? '—' }}</p>
                                <p class="mt-0.5 text-muted">{{ $product->brand?->name ?? 'بدون برند' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-3 py-3">
                                <p class="font-extrabold text-ink">{{ $toman($product->finalPrice()) }}</p>
                                @if ($product->sale_price !== null)
                                    <p class="mt-0.5 text-[10px] text-muted line-through">{{ $toman($product->price) }}</p>
                                @endif
                            </td>
                            <td class="px-3 py-3">
                                <span @class([
                                    'status-pill',
                                    'bg-accent-soft text-accent' => $product->stock <= 5,
                                    'bg-brand-soft text-brand-deep' => $product->stock > 5,
                                ])>
                                    {{ $toPersian($product->stock) }} عدد
                                </span>
                            </td>
                            <td class="px-3 py-3">
                                <span @class([
                                    'status-pill',
                                    'bg-brand-soft text-brand-deep' => $product->is_active,
                                    'bg-canvas text-muted' => ! $product->is_active,
                                ])>
                                    {{ $product->is_active ? 'فعال' : 'غیرفعال' }}
                                </span>
                                @if ($product->is_featured)
                                    <span class="mt-1 block text-[10px] font-bold text-accent">پیشنهاد ویژه</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-brand-deep transition hover:border-brand hover:bg-brand-soft">ویرایش</a>
                                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('محصول و تصاویر آن حذف شود؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-red-100 bg-accent-soft px-2.5 py-1.5 text-[11px] font-bold text-accent transition hover:border-accent hover:bg-accent hover:text-white">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-10 text-center text-xs text-muted">
                                محصولی با این فیلترها پیدا نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="border-t border-line px-4 py-3">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection
