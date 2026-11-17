@extends('layouts.app')

@section('title', 'صفحه پیدا نشد | آسمان‌پوش')
@section('description', 'صفحه‌ای که به دنبال آن هستید وجود ندارد یا جابه‌جا شده است.')

@section('content')
    @include('errors._panel', ['status' => 404])
@endsection
