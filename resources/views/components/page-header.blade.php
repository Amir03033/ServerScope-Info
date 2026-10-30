{{-- Standard page title + intro. Use intro-class="" for full-width intros. --}}
@props(['title', 'intro' => null, 'introClass' => 'max-w-2xl', 'eyebrow' => null])
@if ($eyebrow)
    <p class="ss-eyebrow ss-rise">{{ $eyebrow }}</p>
@endif
<h1 class="ss-rise mt-2 text-4xl font-semibold tracking-[-0.03em] text-fg sm:text-5xl">{{ $title }}</h1>
@if ($intro)
    <p class="ss-rise ss-rise-2 mt-4 text-lg leading-relaxed text-muted {{ $introClass }}">{{ $intro }}</p>
@endif
