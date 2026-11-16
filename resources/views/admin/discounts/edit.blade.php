@extends('layouts.admin')

@section('title', 'ویرایش جشنواره | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'تخفیف‌ها / ویرایش')

@section('page-title', 'ویرایش جشنواره')

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">{{ $discount->title }}</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">زمان، درصد و وضعیت جشنواره را تغییر دهید.</p>
        </div>
        <a href="{{ route('admin.discounts.index') }}" class="btn btn-outline btn-sm">بازگشت به فهرست</a>
    </div>

    <form method="POST" action="{{ route('admin.discounts.update', $discount) }}" class="admin-card max-w-2xl space-y-4 p-5">
        @csrf
        @method('PUT')

        <div>
            <label for="title" class="mb-1.5 block text-xs font-bold text-ink">عنوان جشنواره</label>
            <input id="title" name="title" type="text" value="{{ old('title', $discount->title) }}" class="field" required>
            @error('title')
                <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="percentage" class="mb-1.5 block text-xs font-bold text-ink">درصد تخفیف</label>
                <input id="percentage" name="percentage" type="number" min="1" max="90" value="{{ old('percentage', $discount->percentage) }}" class="field" dir="ltr" required>
                @error('percentage')
                    <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="code" class="mb-1.5 block text-xs font-bold text-ink">کد تخفیف</label>
                <input id="code" name="code" type="text" value="{{ old('code', $discount->code) }}" class="field font-mono" dir="ltr">
                @error('code')
                    <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="starts_at" class="mb-1.5 block text-xs font-bold text-ink">شروع</label>
                <input id="starts_at" name="starts_at" type="datetime-local" value="{{ old('starts_at', $discount->starts_at?->format('Y-m-d\TH:i')) }}" class="field" dir="ltr">
                @error('starts_at')
                    <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="ends_at" class="mb-1.5 block text-xs font-bold text-ink">پایان</label>
                <input id="ends_at" name="ends_at" type="datetime-local" value="{{ old('ends_at', $discount->ends_at?->format('Y-m-d\TH:i')) }}" class="field" dir="ltr">
                @error('ends_at')
                    <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="description" class="mb-1.5 block text-xs font-bold text-ink">توضیحات</label>
            <textarea id="description" name="description" rows="3" class="field">{{ old('description', $discount->description) }}</textarea>
            @error('description')
                <p class="mt-1 text-[11px] font-medium text-accent">{{ $message }}</p>
            @enderror
        </div>

        @include('admin.discounts._scope')

        <label class="flex items-center gap-2 text-xs font-semibold text-ink">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" class="size-4 rounded border-line text-brand-strong focus:ring-brand" @checked(old('is_active', $discount->is_active))>
            جشنواره فعال باشد
        </label>

        <div class="flex flex-col gap-2 pt-1 sm:flex-row">
            <button type="submit" class="btn btn-primary sm:flex-1">ذخیره تغییرات</button>
            <a href="{{ route('admin.discounts.index') }}" class="btn btn-outline sm:flex-1">انصراف</a>
        </div>
    </form>
@endsection
