{{-- Icon in a quiet rounded square. size: sm (2.25rem) | md (2.5rem, default) | lg (3rem) --}}
@props(['name', 'size' => 'md'])
@php
    $sizes = ['sm' => 'h-9 w-9', 'md' => 'h-10 w-10', 'lg' => 'h-12 w-12'];
@endphp
<span {{ $attributes->merge(['class' => 'flex shrink-0 items-center justify-center rounded-[10px] border border-line bg-surface-2 text-fg ' . ($sizes[$size] ?? $sizes['md'])]) }}>
    <x-ss-icon :name="$name" class="h-[18px] w-[18px]" />
</span>
