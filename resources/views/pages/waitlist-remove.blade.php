@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.waitlist.' . $key, [], $lang);
@endphp

@section('title', $t('remove_title') . ' — ServerScope')
@section('noindex', '1')

@section('content')
    <section class="ss-container max-w-2xl pt-16 sm:pt-24">
        <x-page-header :title="$t('remove_title')" />

        @if ($valid ?? false)
            <p class="mt-6 text-muted">{{ $t('remove_text') }}</p>
            <form method="POST" action="{{ url($lang . '/waitlist/remove/' . $entry . '/' . $token) }}" class="mt-6">
                @csrf
                <x-button type="submit">{{ $t('remove_button') }}</x-button>
            </form>
        @else
            <p class="mt-6 text-muted">{{ $t('remove_invalid') }}</p>
            <p class="mt-2 text-sm text-subtle">{{ $t('remove_contact') }} <a class="text-accent underline" href="{{ url($lang . '/contact') }}">{{ trans('site.nav.contact', [], $lang) }}</a></p>
        @endif
    </section>
@endsection
