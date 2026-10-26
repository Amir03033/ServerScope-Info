{{-- Shared error page. The locale comes from the URL, because the locale middleware may not have run. --}}
@extends('layouts.app', ['translated' => false])

@php
    $locale = in_array(request()->segment(1), ['nl', 'en'], true) ? request()->segment(1) : 'nl';
    $t = fn (string $key) => trans('site.errors.' . $key, [], $locale);
@endphp

@section('title', $t($code . '_title') . ' — ServerScope')
@section('robots', 'noindex')

@section('content')
    <section class="relative overflow-hidden">
        <div class="ss-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="ss-container relative max-w-2xl py-24 text-center sm:py-32">
            <p class="font-mono text-sm text-sky-300">{{ $code }}</p>
            <h1 class="ss-h1 mt-4">{{ $t($code . '_title') }}</h1>
            <p class="ss-lead mx-auto mt-5 max-w-md">{{ $t($code . '_text') }}</p>
            <div class="mt-9 flex flex-col justify-center gap-3 sm:flex-row">
                <x-button :href="url($locale)">{{ $t('home') }}</x-button>
                <x-button :href="url($locale . '/docs')" variant="secondary" data-search-open>{{ $t('docs') }}</x-button>
            </div>
        </div>
    </section>
@endsection
