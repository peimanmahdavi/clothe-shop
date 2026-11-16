@extends('layouts.admin')

@section('title', 'ویرایش محصول | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'محصولات / ویرایش')

@section('page-title', 'ویرایش محصول')

@section('content')
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">{{ $product->name }}</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">تغییرات قیمت، موجودی و تصاویر از همین صفحه ذخیره می‌شود.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">بازگشت به فهرست</a>
    </div>

    @include('admin.products._form')
@endsection
