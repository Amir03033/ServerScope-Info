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
    <article class="ss-container max-w-3xl pt-16 sm:pt-24">
        <a href="{{ url($lang . '/blog') }}" class="inline-flex items-center gap-2 text-sm text-muted hover:text-accent">
            <x-ss-icon name="arrow" class="h-4 w-4 rotate-180" /> {{ trans('site.blog.all', [], $lang) }}
        </a>
        <h1 class="mt-6 text-4xl font-semibold leading-tight tracking-tight text-fg sm:text-5xl">{{ $title }}</h1>
        @if ($date)<time class="mt-4 block text-sm text-subtle" datetime="{{ $date }}">{{ \Illuminate\Support\Carbon::parse($date)->locale($lang)->translatedFormat('j F Y') }}</time>@endif

        <x-prose :html="$body" class="mt-10" />
    </article>
@endsection
