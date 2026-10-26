@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.features.' . $key, [], $lang);
    $groups = $t('groups');
@endphp

@section('title', $t('title'))
@section('description', $t('description'))

@section('content')
    <section class="relative overflow-hidden">
        <div class="ss-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="ss-glow pointer-events-none absolute inset-x-0 top-0 h-[30rem]" aria-hidden="true"></div>
        <div class="ss-container relative pb-6 pt-16 sm:pt-20">
            <x-page-header :eyebrow="$t('eyebrow')" :title="$t('headline')" :intro="$t('intro')" />

            <nav class="ss-rise mt-10" style="--rise-delay: 180ms" aria-label="{{ $t('jump') }}">
                <ul class="flex flex-wrap gap-2">
                    @foreach ($groups as [$id, $icon, $title])
                        <li><a href="#{{ $id }}" class="ss-badge ss-tone-muted !px-3 !py-1.5 transition hover:border-sky-400/40 hover:text-white"><x-ss-icon :name="$icon" class="h-3.5 w-3.5" /> {{ $title }}</a></li>
                    @endforeach
                </ul>
            </nav>
        </div>
    </section>

    <div class="ss-container mt-12 grid gap-4">
        @foreach ($groups as [$id, $icon, $title, $text, $points, $slug])
            <section id="{{ $id }}" data-reveal class="ss-card grid gap-8 p-6 sm:p-8 lg:grid-cols-[1fr_1.3fr] lg:gap-12" aria-labelledby="{{ $id }}-title">
                <div>
                    <x-icon-tile :name="$icon" size="lg" />
                    <h2 id="{{ $id }}-title" class="mt-5 text-2xl font-semibold tracking-tight text-white">{{ $title }}</h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-slate-400">{{ $text }}</p>
                    <a href="{{ url($lang . '/docs/' . $slug) }}" class="mt-6 inline-flex items-center gap-1.5 text-sm font-semibold text-sky-300 hover:text-sky-200">
                        {{ $t('read_guide') }} <x-ss-icon name="arrow" class="h-4 w-4" />
                    </a>
                </div>
                <ul class="grid content-start gap-3">
                    @foreach ($points as $point)
                        <li class="flex gap-3 rounded-xl border border-white/[.06] bg-ink-950/40 px-4 py-3 text-[15px] leading-relaxed text-slate-300">
                            <x-ss-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" /> <span>{{ $point }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>

    <section class="ss-container mt-20">
        <div data-reveal class="ss-cta flex flex-col items-start justify-between gap-6 p-8 sm:flex-row sm:items-center sm:p-12">
            <div class="max-w-xl">
                <h2 class="ss-h2">{{ $t('cta_title') }}</h2>
                <p class="ss-lead mt-3">{{ $t('cta_text') }}</p>
            </div>
            <x-button :href="url($lang . '/docs/getting-started')" size="lg" :arrow="true">{{ $t('cta') }}</x-button>
        </div>
    </section>
@endsection
