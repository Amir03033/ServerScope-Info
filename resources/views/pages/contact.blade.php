@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.contact.' . $key, [], $lang);
@endphp

@section('title', $t('title'))

@section('content')
    <section class="mx-auto grid max-w-6xl gap-12 px-4 pb-4 pt-20 sm:px-6 lg:grid-cols-[1.3fr_1fr]">
        <div>
            <x-page-header :title="$t('headline')" :intro="$t('intro')" intro-class="max-w-lg" />

            @if (session('success'))
                <div role="status" class="mt-8 flex items-start gap-3 rounded-xl border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-200">
                    <x-ss-icon name="check" class="mt-0.5 h-5 w-5 shrink-0" /> {{ session('success') ?: $t('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div role="alert" class="mt-8 rounded-xl border border-rose-400/30 bg-rose-400/10 p-4 text-sm text-rose-200">{{ $t('errors') }}</div>
            @endif

            <form method="POST" action="{{ url($lang . '/contact') }}" class="mt-8 space-y-5" novalidate>
                @csrf

                <x-form-field name="name" :label="$t('name')" />
                <x-form-field name="email" type="email" :label="$t('email')" />
                <x-form-field name="subject" :label="$t('subject')" autocomplete="off" />
                <x-form-field name="message" :label="$t('message')" rows="6" />

                <x-button type="submit">{{ $t('send') }}</x-button>
            </form>
        </div>

        <aside class="h-fit rounded-2xl border border-white/10 bg-slate-900/50 p-6">
            <x-icon-tile name="book" />
            <h2 class="mt-5 text-lg font-semibold text-white">{{ $t('aside_title') }}</h2>
            <p class="mt-2 leading-relaxed text-slate-400">{{ $t('aside') }}</p>
            <x-button :href="url($lang . '/docs')" variant="secondary" class="mt-6">{{ $t('docs') }}</x-button>
        </aside>
    </section>
@endsection
