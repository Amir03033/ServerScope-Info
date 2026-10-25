@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.features.' . $key, [], $lang);
@endphp

@section('title', $t('title'))

@section('content')
    <section class="relative overflow-hidden">
        <div class="ss-grid pointer-events-none absolute inset-0"></div>
        <div class="relative mx-auto max-w-6xl px-4 pb-12 pt-20 sm:px-6">
            <h1 class="max-w-3xl text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl">{{ $t('headline') }}</h1>
            <p class="mt-5 max-w-xl text-lg text-slate-400">{{ $t('intro') }}</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($t('items') as [$icon, $title, $text])
                <article class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 transition hover:border-sky-400/30">
                    <x-icon-tile :name="$icon" />
                    <h2 class="mt-5 text-lg font-semibold text-white">{{ $title }}</h2>
                    <p class="mt-2 leading-relaxed text-slate-400">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mx-auto mt-24 max-w-6xl px-4 sm:px-6">
        <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $t('steps_title') }}</h2>
        <ol class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ($t('steps') as $i => [$title, $text])
                <li class="rounded-2xl border border-white/10 bg-[#0e1726] p-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-400 text-sm font-bold text-slate-950">{{ $i + 1 }}</span>
                    <h3 class="mt-4 font-semibold text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
        <p class="mt-6 flex items-start gap-2 text-sm text-slate-500">
            <x-ss-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0" /> {{ $t('note') }}
        </p>
        <div class="mt-10">
            <x-button :href="url($lang . '/docs')">{{ $t('cta') }} <x-ss-icon name="arrow" class="h-4 w-4" /></x-button>
        </div>
    </section>
@endsection
