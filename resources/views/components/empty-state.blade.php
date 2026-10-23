{{-- Dashed placeholder card. Default slot = description; optional "actions" slot. --}}
@props(['icon' => 'book', 'title'])
<div {{ $attributes->merge(['class' => 'rounded-3xl border border-dashed border-white/10 bg-slate-900/40 p-10 text-center']) }}>
    <x-icon-tile :name="$icon" size="lg" class="mx-auto" />
    <h2 class="mt-5 text-xl font-semibold text-white">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-slate-400">{{ $slot }}</p>
    @isset($actions)
        {{ $actions }}
    @endisset
</div>
