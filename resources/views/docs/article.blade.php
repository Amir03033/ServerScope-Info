@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $isEn = $lang === 'en';
    $title = data_get($article ?? null, 'title', $isEn ? 'Documentation' : 'Documentatie');
    $body = data_get($article ?? null, 'html', data_get($article ?? null, 'content', ''));
    $others = collect($articles ?? []);
    $current = data_get($article ?? null, 'slug');
@endphp

@section('title', $title . ' — ServerScope')

@section('content')
    <div class="mx-auto grid max-w-6xl gap-10 px-4 pt-16 sm:px-6 lg:grid-cols-[16rem_1fr]">
        <aside class="lg:sticky lg:top-24 lg:h-fit">
            <a href="{{ url($lang . '/docs') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-sky-300">
                <x-icon name="arrow" class="h-4 w-4 rotate-180" /> {{ $isEn ? 'All documentation' : 'Alle documentatie' }}
            </a>
            @if ($others->isNotEmpty())
                <nav class="mt-5 space-y-1" aria-label="{{ $isEn ? 'Documentation' : 'Documentatie' }}">
                    @foreach ($others as $item)
                        @php $slug = data_get($item, 'slug'); @endphp
                        <a href="{{ url($lang . '/docs/' . $slug) }}"
                           @if($slug === $current) aria-current="page" @endif
                           class="block rounded-lg px-3 py-2 text-sm transition {{ $slug === $current ? 'bg-sky-400/10 font-semibold text-sky-300' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                            {{ data_get($item, 'title', \Illuminate\Support\Str::headline($slug)) }}
                        </a>
                    @endforeach
                </nav>
            @endif
        </aside>

        <article class="min-w-0">
            <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-white">{{ $title }}</h1>
            <div class="ss-prose mt-8">{!! $body !!}</div>
        </article>
    </div>
@endsection
