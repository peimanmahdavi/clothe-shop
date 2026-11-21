@extends('layouts.admin')

@section('title', 'افزودن کاربر | پنل مدیریت')
@section('breadcrumb', 'مدیریت کاربران / افزودن')
@section('page-title', 'افزودن کاربر جدید')

@section('content')
    <div class="mx-auto max-w-4xl">
        @include('admin.users._form')
    </div>
@endsection
