{{-- Dashed placeholder card. Default slot = description; optional "actions" slot. --}}
@props(['icon' => 'book', 'title', 'heading' => 'h2'])
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-white/12 bg-white/[.015] px-6 py-12 text-center']) }}>
    <x-icon-tile :name="$icon" size="lg" class="mx-auto" />
    <{{ $heading }} class="mt-5 text-lg font-semibold text-white">{{ $title }}</{{ $heading }}>
    <p class="mx-auto mt-2 max-w-md text-[15px] leading-relaxed text-slate-400">{{ $slot }}</p>
    @isset($actions)
        <div class="mt-6 flex flex-wrap justify-center gap-3">{{ $actions }}</div>
    @endisset
</div>
