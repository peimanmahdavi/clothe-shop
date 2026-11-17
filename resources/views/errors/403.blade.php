@extends('layouts.app')

@section('title', 'دسترسی غیرمجاز | آسمان‌پوش')
@section('description', 'شما اجازه دسترسی به این صفحه را ندارید.')

@section('content')
    @include('errors._panel', ['status' => 403])
@endsection
