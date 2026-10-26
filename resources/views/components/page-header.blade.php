{{-- Standard page title with optional eyebrow and intro. --}}
@props(['title', 'intro' => null, 'eyebrow' => null, 'introClass' => 'max-w-2xl'])
<div {{ $attributes }}>
    @if ($eyebrow)
        <p class="ss-eyebrow ss-rise">{{ $eyebrow }}</p>
    @endif
    <h1 class="ss-h1 ss-rise mt-4" style="--rise-delay: 60ms">{{ $title }}</h1>
    @if ($intro)
        <p class="ss-lead ss-rise mt-5 {{ $introClass }}" style="--rise-delay: 120ms">{{ $intro }}</p>
    @endif
    {{ $slot }}
</div>
