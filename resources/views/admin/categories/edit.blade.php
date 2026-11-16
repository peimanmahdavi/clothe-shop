@extends('layouts.admin')

@section('title', 'ویرایش دسته‌بندی | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'دسته‌بندی‌ها / ویرایش')

@section('page-title', 'ویرایش دسته‌بندی')

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">{{ $category->name }}</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">تغییر نام، توضیح و ترتیب نمایش دسته‌بندی.</p>
        </div>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline btn-sm">بازگشت به فهرست</a>
    </div>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="admin-card max-w-xl space-y-4 p-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="mb-1.5 block text-xs font-bold text-ink">نام دسته‌بندی</label>
            <input id="name" name="name" type="text" value="{{ old('name', $category->name) }}" class="field" required>
            @error('name')
                <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="mb-1.5 block text-xs font-bold text-ink">توضیح کوتاه</label>
            <textarea id="description" name="description" rows="3" class="field">{{ old('description', $category->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="sort_order" class="mb-1.5 block text-xs font-bold text-ink">ترتیب نمایش</label>
            <input id="sort_order" name="sort_order" type="number" min="0" max="999" value="{{ old('sort_order', $category->sort_order) }}" class="field" dir="ltr">
            @error('sort_order')
                <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-xs font-semibold text-ink">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-line text-brand-strong focus:ring-brand" @checked(old('is_active', $category->is_active))>
            نمایش در فروشگاه
        </label>

        <div class="flex flex-col gap-2 pt-1 sm:flex-row">
            <button type="submit" class="btn btn-primary sm:flex-1">ذخیره تغییرات</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-outline sm:flex-1">انصراف</a>
        </div>
    </form>
@endsection
