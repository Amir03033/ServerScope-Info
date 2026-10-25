{{-- Icon in a tinted rounded square. size: sm (2.5rem) | md (2.75rem, default) | lg (3rem) --}}
@props(['name', 'size' => 'md'])
@php
    $sizes = ['sm' => 'h-10 w-10', 'md' => 'h-11 w-11', 'lg' => 'h-12 w-12'];
@endphp
<span {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center rounded-xl border border-sky-400/20 bg-sky-400/10 text-sky-400 ' . ($sizes[$size] ?? $sizes['md'])]) }}>
    <x-ss-icon :name="$name" />
</span>
