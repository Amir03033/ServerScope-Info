{{-- Icon in a tinted rounded square. size: sm (2.25rem) | md (2.5rem, default) | lg (3rem) --}}
@props(['name', 'size' => 'md'])
@php
    $sizes = ['sm' => 'h-9 w-9 rounded-lg', 'md' => 'h-10 w-10 rounded-xl', 'lg' => 'h-12 w-12 rounded-xl'];
@endphp
<span {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center border border-sky-400/20 bg-sky-400/[.08] text-sky-300 ' . ($sizes[$size] ?? $sizes['md'])]) }}>
    <x-ss-icon :name="$name" :class="$size === 'sm' ? 'h-[18px] w-[18px]' : 'h-5 w-5'" />
</span>
