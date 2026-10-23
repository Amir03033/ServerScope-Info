@props(['href' => null, 'variant' => 'primary', 'type' => 'button'])
@php
    $base = 'inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-sky-400/70 focus-visible:ring-offset-2 focus-visible:ring-offset-[#0b1220]';
    $variants = [
        'primary'   => 'bg-sky-400 text-slate-950 hover:bg-sky-300',
        'secondary' => 'border border-white/10 bg-white/5 text-slate-100 hover:bg-white/10',
        'ghost'     => 'text-slate-300 hover:text-white',
    ];
    $classes = $base . ' ' . ($variants[$variant] ?? $variants['primary']);
@endphp
@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</button>
@endif
