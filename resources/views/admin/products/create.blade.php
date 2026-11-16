@extends('layouts.admin')

@section('title', 'افزودن محصول | پنل مدیریت آسمان‌پوش')

@section('breadcrumb', 'محصولات / افزودن')

@section('page-title', 'افزودن محصول')

@section('content')
    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-xl font-extrabold text-ink sm:text-2xl">محصول جدید</h1>
            <p class="mt-1 text-xs text-muted sm:text-sm">اطلاعات محصول و حداکثر ۳ تصویر آن را وارد کنید.</p>
        </div>
        <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">بازگشت به فهرست</a>
    </div>

    @include('admin.products._form')
@endsection
