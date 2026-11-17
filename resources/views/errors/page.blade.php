@extends('layouts.app')

@section('title', isset($title) && is_string($title) && trim($title) !== '' ? $title : 'خطایی رخ داد | آسمان‌پوش')
@section('description', isset($message) && is_string($message) && trim($message) !== '' ? $message : 'خطایی در فروشگاه آسمان‌پوش رخ داد.')

{{--
    Generic themed error page.

    The backend can pass the error with `with()`:

        return view('errors.page')->with('error', 'متن خطا');

    ...or pass a richer payload:

        return view('errors.page')->with([
            'status' => 403,
            'title' => 'دسترسی غیرمجاز',
            'message' => 'شما اجازه دسترسی به این بخش را ندارید.',
            'hint' => 'در صورت نیاز با مدیر فروشگاه تماس بگیرید.',
        ]);

    A flashed `session('error')` is also picked up automatically.
--}}

@section('content')
    @include('errors._panel', [
        'status' => $status ?? 500,
        'title' => $title ?? null,
        'message' => $message ?? null,
        'hint' => $hint ?? null,
        'error' => $error ?? null,
    ])
@endsection
