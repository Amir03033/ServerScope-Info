@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.contact.' . $key, [], $lang);
    // Pricing buttons link here with ?subject= while registration is closed.
    $subject = mb_substr((string) request()->query('subject', ''), 0, 150);
@endphp

@section('title', $t('title'))
@section('description', $t('intro'))

@section('content')
    <section class="ss-container grid gap-12 pt-16 sm:pt-24 lg:grid-cols-[1.4fr_1fr]">
        <div>
            <x-page-header :title="$t('headline')" :intro="$t('intro')" intro-class="max-w-lg" />

            @if (session('success'))
                <div role="status" class="mt-8 flex items-start gap-3 rounded-xl border border-line bg-surface p-4 text-sm text-fg">
                    <x-ss-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-ok" /> {{ session('success') ?: $t('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mt-8 rounded-xl border border-bad/40 bg-surface p-4 text-sm text-bad">{{ $t('errors') }}</div>
            @endif

            <form method="POST" action="{{ url($lang . '/contact') }}" class="mt-8 max-w-xl space-y-5" novalidate>
                @csrf

                <x-form-field name="name" :label="$t('name')" />
                <x-form-field name="email" type="email" :label="$t('email')" />
                <x-form-field name="subject" :label="$t('subject')" autocomplete="off" :value="$subject" />
                <x-form-field name="message" :label="$t('message')" rows="6" />

                <x-button type="submit">{{ $t('send') }}</x-button>
            </form>
        </div>

        <aside class="ss-card h-fit p-6">
            <x-icon-tile name="book" />
            <h2 class="mt-5 text-lg font-semibold text-fg">{{ $t('aside_title') }}</h2>
            <p class="mt-2 leading-relaxed text-muted">{{ $t('aside') }}</p>
            <x-button :href="url($lang . '/docs')" variant="secondary" class="mt-6">{{ $t('docs') }}</x-button>
        </aside>
    </section>
@endsection
