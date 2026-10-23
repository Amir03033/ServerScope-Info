@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $title = data_get($article ?? null, 'title', trans('site.blog.fallback_title', [], $lang));
    $date = data_get($article ?? null, 'date');
    $excerpt = data_get($article ?? null, 'excerpt');
    $body = data_get($article ?? null, 'html', data_get($article ?? null, 'content', ''));
@endphp

@section('title', $title . ' — ServerScope')
@if (filled($excerpt))
    @section('description', \Illuminate\Support\Str::limit(strip_tags((string) $excerpt), 160))
@endif

@section('content')
    <article class="mx-auto max-w-3xl px-4 pt-16 sm:px-6">
        <a href="{{ url($lang . '/blog') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-sky-300">
            <x-icon name="arrow" class="h-4 w-4 rotate-180" /> {{ trans('site.blog.all', [], $lang) }}
        </a>
        <h1 class="mt-6 text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl">{{ $title }}</h1>
        @if ($date)<time class="mt-4 block text-sm text-slate-500" datetime="{{ $date }}">{{ $date }}</time>@endif

        <x-prose :html="$body" class="mt-10" />
    </article>
@endsection
