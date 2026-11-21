@extends('layouts.admin')

@section('title', 'ویرایش کاربر | پنل مدیریت')
@section('breadcrumb', 'مدیریت کاربران / ویرایش')
@section('page-title', 'ویرایش کاربر')

@section('content')
    <div class="mx-auto max-w-4xl">
        @include('admin.users._form')
    </div>
@endsection
