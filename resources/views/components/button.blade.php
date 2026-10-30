@props(['href' => null, 'variant' => 'primary', 'type' => 'button'])
@php
    $classes = 'ss-btn ' . match ($variant) {
        'secondary' => 'ss-btn-secondary',
        'ghost' => 'ss-btn-ghost',
        default => 'ss-btn-primary',
    };
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
