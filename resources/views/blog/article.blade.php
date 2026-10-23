@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $isEn = $lang === 'en';
    $title = data_get($article ?? null, 'title', 'Blog');
    $date = data_get($article ?? null, 'date');
    $body = data_get($article ?? null, 'html', data_get($article ?? null, 'content', ''));
@endphp

@section('title', $title . ' — ServerScope')

@section('content')
    <article class="mx-auto max-w-3xl px-4 pt-16 sm:px-6">
        <a href="{{ url($lang . '/blog') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-sky-300">
            <x-icon name="arrow" class="h-4 w-4 rotate-180" /> {{ $isEn ? 'All articles' : 'Alle artikelen' }}
        </a>
        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl">{{ $title }}</h1>
        @if ($date)<time class="mt-4 block text-sm text-slate-500">{{ $date }}</time>@endif

        <div class="ss-prose mt-10">{!! $body !!}</div>
    </article>
@endsection
