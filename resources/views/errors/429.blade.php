@extends('layouts.app')

@section('title', 'درخواست‌های بیش از حد | آسمان‌پوش')
@section('description', 'تعداد درخواست‌های شما زیاد است. لطفاً کمی بعد دوباره تلاش کنید.')

@section('content')
    @include('errors._panel', ['status' => 429])
@endsection
