@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.home.' . $key, [], $lang);

    // Illustrative sample data for the previews (the page states "no live data").
    $kpis = [
        [$t('cpu'),    '0.7%',  'sky',     'M0,34 L20,32 L40,35 L55,12 L65,33 L85,30 L110,34 L140,33 L160,36 L200,34', 'cpu'],
        [$t('memory'), '38.6%', 'violet',  'M0,28 L25,22 L45,30 L70,14 L95,26 L120,20 L150,24 L180,16 L200,22',       'memory'],
        [$t('load'),   '0.00',  'emerald', 'M0,36 L30,35 L55,10 L70,34 L110,33 L135,12 L150,34 L200,30',                'chart'],
        [$t('uptime'), $lang === 'en' ? '20d 10h' : '20d 10u', 'amber', 'M0,30 L200,30',                            'clock'],
    ];
    $charts = [
        [$t('cpu'),    '#38bdf8', 'M0,150 L40,148 L70,140 L90,60 L100,148 L150,146 L190,120 L210,148 L300,146 L360,150'],
        [$t('memory'), '#a78bfa', 'M0,92 L60,94 L120,91 L180,93 L240,90 L300,92 L360,91'],
        [$t('load'),   '#4ade80', 'M0,150 L50,140 L80,50 L100,148 L170,130 L220,30 L250,146 L320,100 L360,148'],
    ];
    $tone = [
        'sky'     => ['text-sky-400',     'bg-sky-400/10 border-sky-400/20',         '#38bdf8'],
        'violet'  => ['text-violet-400',  'bg-violet-400/10 border-violet-400/20',   '#a78bfa'],
        'emerald' => ['text-emerald-400', 'bg-emerald-400/10 border-emerald-400/20', '#4ade80'],
        'amber'   => ['text-amber-400',   'bg-amber-400/10 border-amber-400/20',     '#fbbf24'],
    ];
    $servers = [['web-01', true], ['db-01', true], ['worker-01', true], ['backup-01', false]];
    $bentoMetricsPath = 'M0,110 C30,100 50,60 80,70 C110,80 130,30 160,40 C190,50 210,90 240,70 C270,50 300,20 330,30 C350,36 370,50 400,40';
    $steps = trans('site.features.steps', [], $lang);
@endphp

@section('title', $t('title'))
@section('description', $t('description'))

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="ss-grid pointer-events-none absolute inset-0"></div>
        <div class="ss-hero-glow pointer-events-none absolute inset-x-0 top-0 h-[42rem]"></div>

        <div class="relative mx-auto max-w-4xl px-4 pb-16 pt-20 text-center sm:px-6 sm:pt-28">
            <p class="ss-rise inline-flex items-center gap-2 rounded-full border border-sky-400/20 bg-sky-400/10 px-3.5 py-1.5 text-xs font-semibold text-sky-300">
                <span class="h-1.5 w-1.5 rounded-full bg-sky-400" aria-hidden="true"></span> {{ $t('badge') }}
            </p>

            <h1 class="ss-rise ss-rise-2 ss-gradient-text mt-7 pb-2 text-5xl font-extrabold leading-[1.08] tracking-tight sm:text-7xl">
                {{ $t('headline') }}
            </h1>
            <p class="ss-rise ss-rise-3 mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-slate-400 sm:text-xl">{{ $t('intro') }}</p>

            <div class="ss-rise ss-rise-3 mt-10 flex flex-wrap justify-center gap-3">
                <x-button :href="url($lang . '/features')" class="!px-6 !py-3.5">{{ $t('primary') }} <x-ss-icon name="arrow" class="h-4 w-4" /></x-button>
                <x-button :href="url($lang . '/docs')" variant="secondary" class="!px-6 !py-3.5">{{ $t('secondary') }}</x-button>
            </div>

            <ul class="ss-rise ss-rise-4 mt-10 flex flex-wrap justify-center gap-x-7 gap-y-2 text-sm text-slate-400">
                @foreach ($t('points') as $point)
                    <li class="flex items-center gap-2"><x-ss-icon name="check" class="h-4 w-4 text-emerald-400" /> {{ $point }}</li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- Dashboard preview (mirrors the real dashboard layout; sample data only) --}}
    <section class="mx-auto max-w-6xl px-4 sm:px-6" aria-label="{{ $t('preview') }}">
        <div class="ss-window rounded-[1.75rem] p-2 sm:p-3">
            <div class="grid gap-3 lg:grid-cols-[14rem_1fr]">
                <aside class="hidden flex-col justify-between rounded-2xl border border-white/10 bg-[#0a111d] p-4 lg:flex">
                    <div>
                        <div class="flex items-center gap-2.5 px-1">
                            <x-ss-icon name="pulse" class="h-7 w-7 text-sky-400" />
                            <div><p class="text-sm font-extrabold leading-none text-white">ServerScope</p><p class="mt-1 text-[10px] text-slate-500">{{ $t('tagline') }}</p></div>
                        </div>
                        <ul class="mt-6 space-y-1">
                            @foreach (array_combine(['pulse', 'server', 'chart', 'bell', 'cpu'], $t('menu')) as $icon => $label)
                                <li class="flex items-center gap-2.5 rounded-xl px-3 py-2.5 text-sm {{ $loop->first ? 'border border-sky-400/25 bg-sky-400/10 font-semibold text-white' : 'text-slate-400' }}">
                                    <x-ss-icon :name="$icon" class="h-4 w-4" /> {{ $label }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/[.03] p-3">
                        <p class="flex items-center gap-2 text-xs font-semibold text-white"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span> {{ $t('monitoring') }}</p>
                        <p class="mt-1.5 text-[11px] text-slate-500">{{ $t('points')[0] }}</p>
                    </div>
                </aside>

                <div class="space-y-3 rounded-2xl bg-[#0a111d] p-3 sm:p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <p class="text-lg font-extrabold text-white">Dashboard</p>
                        <div class="flex flex-wrap items-center gap-2 text-xs">
                            <span class="flex items-center gap-1.5 rounded-lg border border-emerald-400/25 bg-emerald-400/10 px-2.5 py-1.5 font-semibold text-emerald-300"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span> {{ $t('online') }}</span>
                            <span class="rounded-lg border border-white/10 bg-white/[.04] px-2.5 py-1.5 text-slate-300">{{ $t('range') }}</span>
                            <span class="rounded-lg border border-sky-400/30 bg-sky-400/15 px-2.5 py-1.5 font-semibold text-sky-200">+ {{ $t('add_server') }}</span>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                        @foreach ($kpis as [$label, $value, $color, $points, $icon])
                            <div class="relative overflow-hidden rounded-2xl border border-white/10 ss-card p-4">
                                <div class="flex items-start gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border {{ $tone[$color][1] }} {{ $tone[$color][0] }}"><x-ss-icon :name="$icon" /></span>
                                    <div class="min-w-0"><p class="truncate text-xs text-slate-400">{{ $label }}</p><p class="text-2xl font-bold tracking-tight text-white">{{ $value }}</p></div>
                                </div>
                                <svg viewBox="0 0 200 40" preserveAspectRatio="none" class="mt-3 h-9 w-full" aria-hidden="true"><path d="{{ $points }}" fill="none" stroke="{{ $tone[$color][2] }}" stroke-width="1.8" stroke-linejoin="round" vector-effect="non-scaling-stroke"/></svg>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid gap-3 md:grid-cols-3">
                        @foreach ($charts as [$label, $stroke, $d])
                            <div class="rounded-2xl border border-white/10 bg-white/[.025] p-4">
                                <p class="text-sm font-semibold text-white">{{ $label }}</p>
                                <svg viewBox="0 0 360 160" preserveAspectRatio="none" class="mt-3 h-28 w-full" aria-hidden="true">
                                    <g stroke="rgba(148,163,184,.12)"><path d="M0,40H360M0,80H360M0,120H360"/></g>
                                    <path d="{{ $d }} L360,160 L0,160 Z" fill="{{ $stroke }}" fill-opacity=".10"/>
                                    <path d="{{ $d }}" fill="none" stroke="{{ $stroke }}" stroke-width="1.8" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                                </svg>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid gap-3 md:grid-cols-2">
                        <div class="flex items-center gap-5 rounded-2xl border border-white/10 bg-white/[.025] p-4">
                            <svg viewBox="0 0 42 42" class="h-24 w-24 shrink-0 -rotate-90" aria-hidden="true"><circle cx="21" cy="21" r="15.9" fill="none" stroke="rgba(148,163,184,.18)" stroke-width="4"/><circle cx="21" cy="21" r="15.9" fill="none" stroke="#38bdf8" stroke-width="4" stroke-dasharray="11 89" stroke-linecap="round"/></svg>
                            <div><p class="text-sm font-semibold text-white">{{ $t('disk') }}</p><p class="mt-1 text-3xl font-bold text-white">11%</p><p class="text-xs text-slate-500">{{ $t('used') }}</p></div>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[.025] p-4">
                            <p class="text-sm font-semibold text-white">{{ $t('network') }}</p>
                            <svg viewBox="0 0 360 70" preserveAspectRatio="none" class="mt-3 h-20 w-full" aria-hidden="true"><path d="M0,60 L40,55 L60,30 L80,58 L140,52 L170,20 L200,56 L260,50 L300,35 L330,58 L360,54" fill="none" stroke="#38bdf8" stroke-width="1.8" stroke-linejoin="round" vector-effect="non-scaling-stroke"/><path d="M0,64 L50,60 L90,45 L120,62 L190,58 L230,38 L270,62 L360,60" fill="none" stroke="#a78bfa" stroke-width="1.8" stroke-linejoin="round" vector-effect="non-scaling-stroke"/></svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <p class="mt-4 text-center text-xs text-slate-500">{{ $t('illustrative') }}</p>
    </section>

    {{-- Feature grid --}}
    <section class="mx-auto mt-32 max-w-6xl px-4 sm:px-6">
        <div class="mx-auto max-w-2xl text-center">
            <h2 data-reveal class="text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $t('bento_title') }}</h2>
            <p class="mt-4 text-lg text-slate-400">{{ $t('bento_text') }}</p>
        </div>

        <div class="mt-12 grid gap-4 lg:grid-cols-6" data-reveal-group>
            {{-- Metrics --}}
            <article data-reveal class="ss-card flex flex-col overflow-hidden rounded-3xl p-7 lg:col-span-4">
                <x-icon-tile name="chart" />
                <h3 class="mt-5 text-xl font-semibold text-white">{{ $t('metrics_title') }}</h3>
                <p class="mt-2 max-w-md leading-relaxed text-slate-400">{{ $t('metrics_text') }}</p>
                <svg viewBox="0 0 400 130" preserveAspectRatio="none" class="mt-8 h-32 w-full" aria-hidden="true">
                    <g stroke="rgba(148,163,184,.12)"><path d="M0,35H400M0,70H400M0,105H400"/></g>
                    <path d="{{ $bentoMetricsPath }} L400,130 L0,130 Z" fill="#38bdf8" fill-opacity=".12"/>
                    <path d="{{ $bentoMetricsPath }}" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                </svg>
            </article>

            {{-- Alerts --}}
            <article data-reveal class="ss-card rounded-3xl p-7 lg:col-span-2">
                <x-icon-tile name="bell" />
                <h3 class="mt-5 text-xl font-semibold text-white">{{ $t('alerts_title') }}</h3>
                <p class="mt-2 leading-relaxed text-slate-400">{{ $t('alerts_text') }}</p>
                <ul class="mt-6 space-y-2">
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-[#0e1726] px-3.5 py-2.5 text-sm">
                        <span class="text-slate-300">{{ $t('alert_1') }}</span>
                        <span class="shrink-0 rounded-md border border-emerald-400/20 bg-emerald-400/10 px-2 py-0.5 text-xs font-semibold text-emerald-300">{{ $t('alert_1_state') }}</span>
                    </li>
                    <li class="flex items-center justify-between gap-3 rounded-xl border border-white/10 bg-[#0e1726] px-3.5 py-2.5 text-sm">
                        <span class="text-slate-300">{{ $t('alert_2') }}</span>
                        <span class="shrink-0 rounded-md border border-amber-400/20 bg-amber-400/10 px-2 py-0.5 text-xs font-semibold text-amber-300">{{ $t('alert_2_state') }}</span>
                    </li>
                </ul>
            </article>

            {{-- Servers --}}
            <article data-reveal class="ss-card rounded-3xl p-7 lg:col-span-2">
                <x-icon-tile name="server" />
                <h3 class="mt-5 text-xl font-semibold text-white">{{ $t('servers_title') }}</h3>
                <p class="mt-2 leading-relaxed text-slate-400">{{ $t('servers_text') }}</p>
            </article>

            {{-- Agent --}}
            <article data-reveal class="ss-card rounded-3xl p-7 lg:col-span-2">
                <x-icon-tile name="shield" />
                <h3 class="mt-5 text-xl font-semibold text-white">{{ $t('agent_title') }}</h3>
                <p class="mt-2 leading-relaxed text-slate-400">{{ $t('agent_text') }}</p>
            </article>

            {{-- Languages --}}
            <article data-reveal class="ss-card rounded-3xl p-7 lg:col-span-2">
                <x-icon-tile name="globe" />
                <h3 class="mt-5 text-xl font-semibold text-white">{{ $t('lang_title') }}</h3>
                <p class="mt-2 leading-relaxed text-slate-400">{{ $t('lang_text') }}</p>
            </article>
        </div>
    </section>

    {{-- How it works --}}
    <section class="mx-auto mt-32 max-w-6xl px-4 sm:px-6">
        <h2 class="text-center text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $t('steps_title') }}</h2>
        <ol class="mt-12 grid gap-4 md:grid-cols-3" data-reveal-group>
            @foreach ($steps as $i => [$title, $text])
                <li data-reveal class="ss-card relative rounded-3xl p-7">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-400 text-base font-bold text-slate-950">{{ $i + 1 }}</span>
                    <div class="ss-step-line absolute left-[4.75rem] right-7 top-[3rem] hidden h-px md:block" aria-hidden="true"></div>
                    <h3 class="mt-5 text-lg font-semibold text-white">{{ $title }}</h3>
                    <p class="mt-2 leading-relaxed text-slate-400">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- CTA --}}
    <section class="mx-auto mt-32 max-w-6xl px-4 sm:px-6">
        <div data-reveal class="ss-cta flex flex-col items-start justify-between gap-6 rounded-3xl p-8 sm:flex-row sm:items-center sm:p-14">
            <div class="max-w-xl">
                <h2 class="text-2xl font-bold tracking-tight text-white sm:text-4xl">{{ $t('cta_title') }}</h2>
                <p class="mt-3 text-lg text-slate-400">{{ $t('cta_text') }}</p>
            </div>
            <x-button :href="url($lang . '/docs')" class="shrink-0 !px-6 !py-3.5">{{ $t('cta') }} <x-ss-icon name="arrow" class="h-4 w-4" /></x-button>
        </div>
    </section>
@endsection
