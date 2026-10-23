@extends('layouts.app')

@php
    $isEnglish = ($locale ?? app()->getLocale()) === 'en';
    $lang = $isEnglish ? 'en' : 'nl';

    $copy = [
        'nl' => [
            'title' => 'ServerScope — Krijg inzicht in je servers',
            'description' => 'Beheer je servers, bekijk metrics en volg waarschuwingen vanuit één overzichtelijk platform.',
            'headline' => 'Je servers, helder in beeld.',
            'intro' => 'Krijg inzicht in je serverinfrastructuur met een overzichtelijke omgeving voor serverbeheer, metrics en waarschuwingen.',
            'primary' => 'Ontdek de functionaliteiten',
            'secondary' => 'Lees de documentatie',
            'preview' => 'Infrastructuuroverzicht',
            'illustrative' => 'Illustratief voorbeeld. Geen live data.',
            'cpu' => 'CPU-gebruik', 'memory' => 'Geheugengebruik', 'load' => 'Load average', 'uptime' => 'Uptime',
            'section_title' => 'Je infrastructuur. Eén helder overzicht.',
            'section_text' => 'ServerScope brengt serverinformatie overzichtelijk samen.',
            'features' => [
                ['server', 'Serverbeheer', 'Beheer geregistreerde servers vanuit één centrale omgeving.'],
                ['chart',  'Metrics en inzicht', 'Breng beschikbare servermetingen overzichtelijk in beeld.'],
                ['bell',   'Waarschuwingen', 'Houd belangrijke meldingen en hun geschiedenis bij.'],
            ],
            'cta_title' => 'Krijg meer grip op je serveromgeving.',
            'cta_text' => 'Bekijk de documentatie en ontdek hoe ServerScope is opgebouwd.',
            'cta' => 'Bekijk de documentatie',
        ],
        'en' => [
            'title' => 'ServerScope — Understand your infrastructure',
            'description' => 'Manage servers, inspect metrics and track alerts from one clear platform.',
            'headline' => 'Your servers, clearly in view.',
            'intro' => 'Get insight into your server infrastructure with one clear environment for server management, metrics and alerts.',
            'primary' => 'Explore the features',
            'secondary' => 'Read the documentation',
            'preview' => 'Infrastructure overview',
            'illustrative' => 'Illustrative example. No live data.',
            'cpu' => 'CPU usage', 'memory' => 'Memory usage', 'load' => 'Load average', 'uptime' => 'Uptime',
            'section_title' => 'Your infrastructure. One clear overview.',
            'section_text' => 'ServerScope brings server information together in one place.',
            'features' => [
                ['server', 'Server management', 'Manage registered servers from one central environment.'],
                ['chart',  'Metrics and insight', 'Show available server measurements in a clear overview.'],
                ['bell',   'Alerts', 'Keep track of important notifications and their history.'],
            ],
            'cta_title' => 'Get a better grip on your server environment.',
            'cta_text' => 'Read the documentation and see how ServerScope is built.',
            'cta' => 'Read the documentation',
        ],
    ];
    $t = $copy[$lang];

    $kpis = [
        [$t['cpu'],    '0.7%',  'sky',     'M0,34 L20,32 L40,35 L55,12 L65,33 L85,30 L110,34 L140,33 L160,36 L200,34', 'cpu'],
        [$t['memory'], '38.6%', 'violet',  'M0,28 L25,22 L45,30 L70,14 L95,26 L120,20 L150,24 L180,16 L200,22',       'memory'],
        [$t['load'],   '0.00',  'emerald', 'M0,36 L30,35 L55,10 L70,34 L110,33 L135,12 L150,34 L200,30',                'chart'],
        [$t['uptime'], '20d 10u', 'amber', 'M0,30 L200,30',                                                             'clock'],
    ];
    $tone = [
        'sky'     => ['text-sky-400',     'bg-sky-400/10 border-sky-400/20',         '#38bdf8'],
        'violet'  => ['text-violet-400',  'bg-violet-400/10 border-violet-400/20',   '#a78bfa'],
        'emerald' => ['text-emerald-400', 'bg-emerald-400/10 border-emerald-400/20', '#4ade80'],
        'amber'   => ['text-amber-400',   'bg-amber-400/10 border-amber-400/20',     '#fbbf24'],
    ];
@endphp

@section('title', $t['title'])
@section('description', $t['description'])

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="ss-grid pointer-events-none absolute inset-0"></div>
        <div class="pointer-events-none absolute -top-40 left-1/2 h-96 w-[48rem] -translate-x-1/2 rounded-full bg-sky-500/10 blur-3xl"></div>

        <div class="relative mx-auto max-w-6xl px-4 pb-10 pt-20 sm:px-6 sm:pt-28">
            <h1 class="max-w-3xl text-4xl font-extrabold leading-[1.08] tracking-tight text-white sm:text-6xl">
                {{ $t['headline'] }}
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-relaxed text-slate-400">{{ $t['intro'] }}</p>
            <div class="mt-9 flex flex-wrap gap-3">
                <x-button :href="url($lang . '/features')">{{ $t['primary'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button>
                <x-button :href="url($lang . '/docs')" variant="secondary">{{ $t['secondary'] }}</x-button>
            </div>
        </div>
    </section>

    {{-- Dashboardvoorbeeld --}}
    <section class="mx-auto max-w-6xl px-4 sm:px-6" aria-label="{{ $t['preview'] }}">
        <div class="rounded-3xl border border-white/10 bg-slate-900/60 p-3 shadow-2xl shadow-sky-500/5 sm:p-5">
            <div class="flex items-center justify-between px-2 pb-4 pt-1">
                <div class="flex items-center gap-2" aria-hidden="true">
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-700"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-700"></span>
                    <span class="h-2.5 w-2.5 rounded-full bg-slate-700"></span>
                </div>
                <p class="text-xs font-medium text-slate-500">{{ $t['preview'] }}</p>
                <span class="flex items-center gap-1.5 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-2.5 py-1 text-xs font-semibold text-emerald-300">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Online
                </span>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($kpis as [$label, $value, $color, $points, $icon])
                    <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-[#0e1726] p-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-10 w-10 items-center justify-center rounded-xl border {{ $tone[$color][1] }} {{ $tone[$color][0] }}">
                                <x-icon :name="$icon" />
                            </span>
                            <div>
                                <p class="text-xs text-slate-400">{{ $label }}</p>
                                <p class="text-2xl font-bold tracking-tight text-white">{{ $value }}</p>
                            </div>
                        </div>
                        <svg viewBox="0 0 200 40" preserveAspectRatio="none" class="mt-4 h-10 w-full" aria-hidden="true">
                            <path d="{{ $points }}" fill="none" stroke="{{ $tone[$color][2] }}" stroke-width="1.8" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                        </svg>
                    </div>
                @endforeach
            </div>

            <div class="mt-3 grid gap-3 lg:grid-cols-3">
                @foreach ([[$t['cpu'], '#38bdf8', 'M0,150 L40,148 L70,140 L90,60 L100,148 L150,146 L190,120 L210,148 L300,146 L360,150'],
                           [$t['memory'], '#a78bfa', 'M0,92 L60,94 L120,91 L180,93 L240,90 L300,92 L360,91'],
                           [$t['load'], '#4ade80', 'M0,150 L50,140 L80,50 L100,148 L170,130 L220,30 L250,146 L320,100 L360,148']] as [$label, $stroke, $d])
                    <div class="rounded-2xl border border-white/10 bg-[#0e1726] p-4">
                        <p class="text-sm font-semibold text-white">{{ $label }}</p>
                        <svg viewBox="0 0 360 160" preserveAspectRatio="none" class="mt-3 h-32 w-full" aria-hidden="true">
                            <g stroke="rgba(148,163,184,.12)"><path d="M0,40H360M0,80H360M0,120H360"/></g>
                            <path d="{{ $d }} L360,160 L0,160 Z" fill="{{ $stroke }}" fill-opacity=".10"/>
                            <path d="{{ $d }}" fill="none" stroke="{{ $stroke }}" stroke-width="1.8" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                        </svg>
                    </div>
                @endforeach
            </div>
        </div>
        <p class="mt-3 text-center text-xs text-slate-500">{{ $t['illustrative'] }}</p>
    </section>

    {{-- Functies --}}
    <section class="mx-auto mt-28 max-w-6xl px-4 sm:px-6">
        <div class="max-w-2xl">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">{{ $t['section_title'] }}</h2>
            <p class="mt-4 text-lg text-slate-400">{{ $t['section_text'] }}</p>
        </div>
        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($t['features'] as [$icon, $title, $text])
                <article class="rounded-2xl border border-white/10 bg-slate-900/50 p-6">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-sky-400/20 bg-sky-400/10 text-sky-400">
                        <x-icon :name="$icon" class="h-5 w-5" />
                    </span>
                    <h3 class="mt-5 text-lg font-semibold text-white">{{ $title }}</h3>
                    <p class="mt-2 leading-relaxed text-slate-400">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto mt-28 max-w-6xl px-4 sm:px-6">
        <div class="flex flex-col items-start justify-between gap-6 rounded-3xl border border-sky-400/20 bg-gradient-to-br from-sky-400/10 via-slate-900/60 to-violet-400/10 p-8 sm:flex-row sm:items-center sm:p-12">
            <div class="max-w-xl">
                <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $t['cta_title'] }}</h2>
                <p class="mt-3 text-slate-400">{{ $t['cta_text'] }}</p>
            </div>
            <x-button :href="url($lang . '/docs')" class="shrink-0">{{ $t['cta'] }}</x-button>
        </div>
    </section>
@endsection
