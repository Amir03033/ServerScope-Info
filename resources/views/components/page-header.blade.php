{{-- Standard page title + intro. Use intro-class="" for full-width intros. --}}
@props(['title', 'intro' => null, 'introClass' => 'max-w-xl'])
<h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $title }}</h1>
@if ($intro)
    <p class="mt-4 text-lg text-slate-400 {{ $introClass }}">{{ $intro }}</p>
@endif
