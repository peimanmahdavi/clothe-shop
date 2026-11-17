@extends('layouts.app')

@section('title', 'خطایی رخ داد | آسمان‌پوش')
@section('description', 'درخواست شما قابل پردازش نیست. لطفاً دوباره تلاش کنید.')

@section('content')
    @include('errors._panel', [
        'status' => isset($exception) && method_exists($exception, 'getStatusCode') ? $exception->getStatusCode() : 400,
    ])
@endsection
