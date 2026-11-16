@extends('layouts.admin')

@section('title', 'دسته‌بندی‌ها | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'دسته‌بندی‌ها')

@section('page-title', 'مدیریت دسته‌بندی‌ها')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">دسته‌بندی‌ها</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">{{ $toPersian($categories->count()) }} دسته‌بندی ثبت شده است.</p>
        </div>
    </div>

    <div class="mt-4 grid items-start gap-4 lg:grid-cols-3">
        <section class="admin-card overflow-hidden lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[560px] text-sm">
                    <thead class="bg-canvas/70 text-[11px] text-muted">
                        <tr>
                            <th class="px-5 py-3 text-start font-bold">دسته‌بندی</th>
                            <th class="px-3 py-3 text-start font-bold">ترتیب</th>
                            <th class="px-3 py-3 text-start font-bold">محصولات</th>
                            <th class="px-3 py-3 text-start font-bold">وضعیت</th>
                            <th class="px-5 py-3 text-end font-bold">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($categories as $category)
                            <tr class="transition-colors hover:bg-canvas/60">
                                <td class="px-5 py-3">
                                    <p class="font-bold text-ink">{{ $category->name }}</p>
                                    @if ($category->description)
                                        <p class="mt-0.5 max-w-xs truncate text-[11px] text-muted">{{ $category->description }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-xs text-muted">{{ $toPersian($category->sort_order) }}</td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full bg-brand-soft px-2.5 py-1 text-[11px] font-bold text-brand-deep">{{ $toPersian($category->products_count) }} محصول</span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="status-pill {{ $category->is_active ? 'bg-brand-soft text-brand-deep' : 'bg-canvas text-muted' }}">
                                        {{ $category->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">ویرایش</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('دسته‌بندی «{{ $category->name }}» حذف شود؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-100 bg-accent-soft px-2.5 py-1.5 text-[11px] font-bold text-accent transition hover:border-accent hover:bg-accent hover:text-white">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-xs text-muted">هنوز دسته‌بندی‌ای ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card p-5">
            <h2 class="text-sm font-extrabold text-ink">دسته‌بندی جدید</h2>
            <p class="mt-1 text-[11px] text-muted">نام و ترتیب نمایش را مشخص کنید.</p>

            <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-4 space-y-3">
                @csrf

                <div>
                    <label for="name" class="mb-1.5 block text-xs font-bold text-ink">نام دسته‌بندی</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="field" placeholder="مثلاً پوشاک مردانه" required>
                    @error('name')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="mb-1.5 block text-xs font-bold text-ink">توضیح کوتاه</label>
                    <textarea id="description" name="description" rows="2" class="field" placeholder="این دسته شامل چه محصولاتی است؟">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="sort_order" class="mb-1.5 block text-xs font-bold text-ink">ترتیب نمایش</label>
                    <input id="sort_order" name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', 0) }}" class="field" dir="ltr">
                    @error('sort_order')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-xs font-semibold text-ink">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-line text-brand-strong focus:ring-brand" @checked(old('is_active', true))>
                    نمایش در فروشگاه
                </label>

                <button type="submit" class="btn btn-primary w-full">افزودن دسته‌بندی</button>
            </form>
        </section>
    </div>
@endsection
