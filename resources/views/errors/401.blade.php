@extends('layouts.app')

@section('title', 'ورود لازم است | آسمان‌پوش')
@section('description', 'برای دسترسی به این بخش ابتدا وارد حساب کاربری خود شوید.')

@section('content')
    @include('errors._panel', ['status' => 401])
@endsection
