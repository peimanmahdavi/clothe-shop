@extends('layouts.app')

@section('title', 'خطای سرور | آسمان‌پوش')
@section('description', 'مشکلی در سرور رخ داده است. لطفاً کمی بعد دوباره تلاش کنید.')

@section('content')
    @include('errors._panel', ['status' => 500])
@endsection
