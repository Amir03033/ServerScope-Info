{{-- Button or link styled as a button. variant: primary | secondary | ghost; size: sm | md | lg --}}
@props(['href' => null, 'variant' => 'primary', 'size' => 'md', 'type' => 'button', 'arrow' => false])
@php
    $classes = 'ss-btn ss-btn-' . (in_array($variant, ['primary', 'secondary', 'ghost'], true) ? $variant : 'primary')
        . ($size === 'md' ? '' : ' ss-btn-' . $size);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}@if ($arrow)<x-ss-icon name="arrow" class="h-4 w-4" data-arrow />@endif</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}@if ($arrow)<x-ss-icon name="arrow" class="h-4 w-4" data-arrow />@endif</button>
@endif
