{{-- Dashed placeholder card. Default slot = description; optional "actions" slot. --}}
@props(['icon' => 'book', 'title'])
<div {{ $attributes->merge(['class' => 'rounded-2xl border border-dashed border-line-strong p-10 text-center']) }}>
    <x-icon-tile :name="$icon" size="lg" class="mx-auto" />
    <h2 class="mt-5 text-lg font-semibold text-fg">{{ $title }}</h2>
    <p class="mx-auto mt-2 max-w-md text-muted">{{ $slot }}</p>
    @isset($actions)
        {{ $actions }}
    @endisset
</div>
