@extends('layouts.app')

@section('title', 'صفحه منقضی شده | آسمان‌پوش')
@section('description', 'نشست شما منقضی شده است. لطفاً صفحه را دوباره بارگذاری کنید.')

@section('content')
    @include('errors._panel', ['status' => 419])
@endsection
