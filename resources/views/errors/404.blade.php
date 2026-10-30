@extends('layouts.app')

@php
    // No route matched, so the locale comes from the first part of the address.
    $locale = request()->segment(1) === 'en' ? 'en' : 'nl';
    $lang = $locale;
    $e = fn (string $key) => trans('site.errors.' . $key, [], $lang);
    $links = [
        ['', trans('site.docs.home', [], $lang)],
        ['/docs', trans('site.docs.headline', [], $lang)],
        ['/features', trans('site.features.headline', [], $lang)],
        ['/pricing', trans('site.pricing.headline', [], $lang)],
        ['/contact', trans('site.contact.headline', [], $lang)],
    ];
@endphp

@section('title', $e('title'))
@section('description', $e('text'))
@section('noindex', '1')

@section('content')
    <section class="ss-container max-w-3xl pb-24 pt-16 sm:pt-24">
        <x-page-header :eyebrow="$e('eyebrow')" :title="$e('headline')" :intro="$e('text')" />

        <form method="GET" action="{{ url($lang . '/docs') }}" role="search" class="ss-rise ss-rise-3 mt-8 flex max-w-xl gap-2">
            <label for="notfound-q" class="sr-only">{{ $e('search_label') }}</label>
            <input id="notfound-q" type="search" name="q" placeholder="{{ $e('search_label') }}" class="ss-input min-w-0 flex-1">
            <x-button type="submit">{{ $e('search_button') }}</x-button>
        </form>

        <h2 class="mt-12 text-sm font-semibold uppercase tracking-wider text-subtle">{{ $e('links') }}</h2>
        <ul class="mt-4 grid gap-2 sm:grid-cols-2">
            @foreach ($links as [$path, $label])
                <li>
                    <a href="{{ url($lang . $path) }}" class="ss-card flex items-center justify-between gap-3 px-4 py-3 text-fg">
                        <span>{{ $label }}</span>
                        <x-ss-icon name="arrow" class="h-4 w-4 text-subtle" />
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endsection
