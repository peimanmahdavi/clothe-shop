@props([
    'status' => 'pending',
    'label' => null,
])

@php
    $styles = [
        'paid' => 'bg-green-100 text-green-700',
        'completed' => 'bg-green-100 text-green-700',
        'pending' => 'bg-amber-100 text-amber-700',
        'processing' => 'bg-brand-soft text-brand-deep',
        'shipped' => 'bg-blue-100 text-blue-700',
        'failed' => 'bg-red-100 text-red-700',
        'cancelled' => 'bg-red-100 text-red-700',
        'refunded' => 'bg-slate-200 text-slate-700',
    ];

    $labels = [
        'paid' => 'موفق',
        'pending' => 'در انتظار',
        'failed' => 'ناموفق',
        'refunded' => 'مسترد شده',
        'completed' => 'تکمیل شده',
        'processing' => 'در حال پردازش',
        'shipped' => 'ارسال شده',
        'cancelled' => 'لغو شده',
    ];

    $text = $label ?? ($labels[$status] ?? $status);
    $class = $styles[$status] ?? 'bg-canvas text-muted';
@endphp

<span {{ $attributes->merge(['class' => "status-pill {$class}"]) }}>{{ $text }}</span>
