{{-- Heading for a page section: eyebrow, title (h2), optional text and action slot. --}}
@props(['eyebrow' => null, 'title', 'text' => null, 'align' => 'left', 'id' => null])
<div {{ $attributes->merge(['class' => 'flex flex-wrap items-end justify-between gap-x-8 gap-y-5' . ($align === 'center' ? ' text-center justify-center' : '')]) }}>
    <div class="{{ $align === 'center' ? 'mx-auto max-w-2xl' : 'max-w-2xl' }}">
        @if ($eyebrow)<p class="ss-eyebrow">{{ $eyebrow }}</p>@endif
        <h2 @if ($id) id="{{ $id }}" @endif class="ss-h2 mt-3">{{ $title }}</h2>
        @if ($text)<p class="ss-lead mt-4">{{ $text }}</p>@endif
    </div>
    @isset($action)
        <div class="shrink-0">{{ $action }}</div>
    @endisset
</div>
