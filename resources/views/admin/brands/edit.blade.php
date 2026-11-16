@extends('layouts.admin')

@section('title', 'ویرایش برند | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'برندها / ویرایش')

@section('page-title', 'ویرایش برند')

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">{{ $brand->name }}</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">اطلاعات برند را به‌روز کنید.</p>
        </div>
        <a href="{{ route('admin.brands.index') }}" class="btn btn-outline btn-sm">بازگشت به فهرست</a>
    </div>

    <form method="POST" action="{{ route('admin.brands.update', $brand) }}" class="admin-card max-w-xl space-y-4 p-5">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="mb-1.5 block text-xs font-bold text-ink">نام برند</label>
            <input id="name" name="name" type="text" value="{{ old('name', $brand->name) }}" class="field" required>
            @error('name')
                <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="description" class="mb-1.5 block text-xs font-bold text-ink">توضیح کوتاه</label>
            <textarea id="description" name="description" rows="3" class="field">{{ old('description', $brand->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
            @enderror
        </div>

        <label class="flex items-center gap-2 text-xs font-semibold text-ink">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-line text-brand-strong focus:ring-brand" @checked(old('is_active', $brand->is_active))>
            نمایش در فروشگاه
        </label>

        <div class="flex flex-col gap-2 pt-1 sm:flex-row">
            <button type="submit" class="btn btn-primary sm:flex-1">ذخیره تغییرات</button>
            <a href="{{ route('admin.brands.index') }}" class="btn btn-outline sm:flex-1">انصراف</a>
        </div>
    </form>
@endsection
