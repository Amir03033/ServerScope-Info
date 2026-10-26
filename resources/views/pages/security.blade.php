@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.security.' . $key, [], $lang);
@endphp

@section('title', $t('title'))
@section('description', $t('description'))

@section('content')
    <section class="relative overflow-hidden">
        <div class="ss-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="ss-container relative pt-16 sm:pt-20">
            <x-page-header :eyebrow="$t('eyebrow')" :title="$t('headline')" :intro="$t('intro')" />
        </div>
    </section>

    <div class="ss-container mt-12 grid gap-4 lg:grid-cols-[1.2fr_1fr]">
        <section class="ss-cta p-6 sm:p-8" aria-labelledby="report-title" data-reveal>
            <x-icon-tile name="shield" size="lg" />
            <h2 id="report-title" class="mt-5 text-2xl font-semibold tracking-tight text-white">{{ $t('report_title') }}</h2>
            <p class="mt-3 text-[15px] leading-relaxed text-slate-300">{{ $t('report_text') }}</p>
            <ol class="mt-6 space-y-3">
                @foreach ($t('report_points') as $i => $point)
                    <li class="flex gap-3 text-[15px] leading-relaxed text-slate-300">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-sky-400/30 font-mono text-xs text-sky-300">{{ $i + 1 }}</span> {{ $point }}
                    </li>
                @endforeach
            </ol>
            <x-button :href="url($lang . '/contact') . '?' . http_build_query(['subject' => $t('report_subject')])" class="mt-7" :arrow="true">{{ $t('report_cta') }}</x-button>
        </section>

        <section class="ss-panel p-6 sm:p-8" aria-labelledby="tips-title" data-reveal>
            <x-icon-tile name="key" size="lg" />
            <h2 id="tips-title" class="mt-5 text-2xl font-semibold tracking-tight text-white">{{ $t('tips_title') }}</h2>
            <ul class="mt-6 space-y-3">
                @foreach ($t('tips') as $tip)
                    <li class="flex gap-3 text-[15px] leading-relaxed text-slate-300"><x-ss-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" /> {{ $tip }}</li>
                @endforeach
            </ul>
        </section>
    </div>

    <section class="ss-container mt-20" aria-labelledby="measures-title">
        <x-section-heading id="measures-title" :title="$t('measures_title')" :text="$t('measures_intro')" data-reveal />
        <ul class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach ($t('measures') as [$icon, $title, $text, $slug])
                <li data-reveal>
                    <a href="{{ url($lang . '/docs/' . $slug) }}" class="ss-card group flex h-full flex-col p-6">
                        <x-icon-tile :name="$icon" />
                        <h3 class="mt-5 font-semibold text-white">{{ $title }}</h3>
                        <p class="mt-2 flex-1 text-[15px] leading-relaxed text-slate-400">{{ $text }}</p>
                        <x-ss-icon name="arrow" class="mt-5 h-4 w-4 text-slate-600 transition group-hover:translate-x-1 group-hover:text-sky-300" />
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    <div class="ss-container mt-16 grid gap-4 md:grid-cols-2">
        <section class="ss-panel p-6" aria-labelledby="site-title">
            <h2 id="site-title" class="font-semibold text-white">{{ $t('site_title') }}</h2>
            <ul class="mt-4 space-y-2.5">
                @foreach ($t('site_points') as $point)
                    <li class="flex gap-3 text-sm leading-relaxed text-slate-400"><x-ss-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-slate-500" /> {{ $point }}</li>
                @endforeach
            </ul>
        </section>
        <section class="rounded-2xl border border-dashed border-white/12 p-6" aria-labelledby="confirm-title">
            <h2 id="confirm-title" class="flex items-center gap-2 font-semibold text-white"><x-ss-icon name="info" class="h-4 w-4 text-slate-400" /> {{ $t('confirm_title') }}</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-400">{{ $t('confirm_text') }}</p>
        </section>
    </div>
@endsection
