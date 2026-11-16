@extends('layouts.admin')

@section('title', 'برندها | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'برندها')

@section('page-title', 'مدیریت برندها')

@section('content')
    @php
        $toPersian = static fn (string|int $value): string => strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    @endphp

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">برندها</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">{{ $toPersian($brands->count()) }} برند در فروشگاه ثبت شده است.</p>
        </div>
    </div>

    <div class="mt-4 grid items-start gap-4 lg:grid-cols-3">
        <section class="admin-card overflow-hidden lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] text-sm">
                    <thead class="bg-canvas/70 text-[11px] text-muted">
                        <tr>
                            <th class="px-5 py-3 text-start font-bold">برند</th>
                            <th class="px-3 py-3 text-start font-bold">محصولات</th>
                            <th class="px-3 py-3 text-start font-bold">وضعیت</th>
                            <th class="px-5 py-3 text-end font-bold">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($brands as $brand)
                            <tr class="transition-colors hover:bg-canvas/60">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-xs font-extrabold text-brand-deep">{{ mb_substr($brand->name, 0, 1) }}</span>
                                        <div>
                                            <p class="font-bold text-ink">{{ $brand->name }}</p>
                                            @if ($brand->description)
                                                <p class="mt-0.5 max-w-xs truncate text-[11px] text-muted">{{ $brand->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="rounded-full bg-brand-soft px-2.5 py-1 text-[11px] font-bold text-brand-deep">{{ $toPersian($brand->products_count) }} محصول</span>
                                </td>
                                <td class="px-3 py-3">
                                    <span class="status-pill {{ $brand->is_active ? 'bg-brand-soft text-brand-deep' : 'bg-canvas text-muted' }}">
                                        {{ $brand->is_active ? 'فعال' : 'غیرفعال' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.brands.edit', $brand) }}" class="rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-bold text-ink transition hover:border-brand hover:text-brand-deep">ویرایش</a>
                                        <form method="POST" action="{{ route('admin.brands.destroy', $brand) }}" onsubmit="return confirm('برند «{{ $brand->name }}» حذف شود؟')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-100 bg-accent-soft px-2.5 py-1.5 text-[11px] font-bold text-accent transition hover:border-accent hover:bg-accent hover:text-white">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-xs text-muted">هنوز برندی ثبت نشده است.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-card p-5">
            <h2 class="text-sm font-extrabold text-ink">برند جدید</h2>
            <p class="mt-1 text-[11px] text-muted">برند را به محصولات اختصاص دهید.</p>

            <form method="POST" action="{{ route('admin.brands.store') }}" class="mt-4 space-y-3">
                @csrf

                <div>
                    <label for="name" class="mb-1.5 block text-xs font-bold text-ink">نام برند</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" class="field" placeholder="مثلاً آسمان‌پوش" required>
                    @error('name')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="mb-1.5 block text-xs font-bold text-ink">توضیح کوتاه</label>
                    <textarea id="description" name="description" rows="2" class="field">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                    @enderror
                </div>

                <label class="flex items-center gap-2 text-xs font-semibold text-ink">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-line text-brand-strong focus:ring-brand" @checked(old('is_active', true))>
                    نمایش در فروشگاه
                </label>

                <button type="submit" class="btn btn-primary w-full">افزودن برند</button>
            </form>
        </section>
    </div>
@endsection
