@extends('layouts.app')

@section('title', 'موقتاً در دسترس نیست | آسمان‌پوش')
@section('description', 'فروشگاه در حال به‌روزرسانی است. لطفاً بعداً مراجعه کنید.')

@section('content')
    @include('errors._panel', ['status' => 503])
@endsection
