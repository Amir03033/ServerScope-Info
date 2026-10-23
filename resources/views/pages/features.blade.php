@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $isEn = $lang === 'en';

    $t = $isEn ? [
        'title' => 'Features — ServerScope',
        'headline' => 'What ServerScope can offer',
        'intro' => 'One environment to manage servers, view metrics and follow alerts.',
        'steps_title' => 'How the platform is designed to work',
        'steps' => [
            ['Register a server', 'Add a server and give it a recognisable name.'],
            ['Connect the agent', 'A small agent sends server data to ServerScope over HTTPS.'],
            ['Follow the dashboard', 'Metrics, system information and alerts appear in one overview.'],
        ],
        'note' => 'The website describes the platform. Which monitoring features are operational is determined separately.',
        'cta' => 'Read the documentation',
        'items' => [
            ['server',  'Server management', 'Register servers and keep their basic details, such as hostname, IP address and operating system, together.'],
            ['cpu',     'CPU and load', 'Follow processor usage and the load average over a selected period.'],
            ['memory',  'Memory', 'See how much memory is in use and how that changes over time.'],
            ['network', 'Network activity', 'Keep an eye on download and upload traffic per server.'],
            ['bell',    'Alerts and rules', 'Define rules and keep a history of the alerts they trigger.'],
            ['globe',   'Two languages', 'The interface and documentation are available in Dutch and English.'],
        ],
    ] : [
        'title' => 'Functionaliteiten — ServerScope',
        'headline' => 'Wat ServerScope kan bieden',
        'intro' => 'Eén omgeving om servers te beheren, metrics te bekijken en waarschuwingen te volgen.',
        'steps_title' => 'Zo is het platform bedoeld te werken',
        'steps' => [
            ['Registreer een server', 'Voeg een server toe en geef hem een herkenbare naam.'],
            ['Verbind de agent', 'Een kleine agent stuurt servergegevens via HTTPS naar ServerScope.'],
            ['Volg het dashboard', 'Metrics, systeeminformatie en waarschuwingen staan bij elkaar in één overzicht.'],
        ],
        'note' => 'De website beschrijft het platform. Welke monitoringfuncties daadwerkelijk operationeel zijn, wordt apart vastgesteld.',
        'cta' => 'Lees de documentatie',
        'items' => [
            ['server',  'Serverbeheer', 'Registreer servers en houd basisgegevens zoals hostnaam, IP-adres en besturingssysteem bij elkaar.'],
            ['cpu',     'CPU en load', 'Volg het processorgebruik en de load average over een gekozen periode.'],
            ['memory',  'Geheugen', 'Zie hoeveel geheugen in gebruik is en hoe dat in de tijd verloopt.'],
            ['network', 'Netwerkactiviteit', 'Houd download- en uploadverkeer per server in de gaten.'],
            ['bell',    'Waarschuwingen en regels', 'Stel regels in en bewaar de geschiedenis van de waarschuwingen die ze activeren.'],
            ['globe',   'Twee talen', 'De interface en documentatie zijn beschikbaar in het Nederlands en Engels.'],
        ],
    ];
@endphp

@section('title', $t['title'])

@section('content')
    <section class="relative overflow-hidden">
        <div class="ss-grid pointer-events-none absolute inset-0"></div>
        <div class="relative mx-auto max-w-6xl px-4 pb-12 pt-20 sm:px-6">
            <h1 class="max-w-3xl text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl">{{ $t['headline'] }}</h1>
            <p class="mt-5 max-w-xl text-lg text-slate-400">{{ $t['intro'] }}</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($t['items'] as [$icon, $title, $text])
                <article class="rounded-2xl border border-white/10 bg-slate-900/50 p-6 transition hover:border-sky-400/30">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl border border-sky-400/20 bg-sky-400/10 text-sky-400">
                        <x-icon :name="$icon" />
                    </span>
                    <h2 class="mt-5 text-lg font-semibold text-white">{{ $title }}</h2>
                    <p class="mt-2 leading-relaxed text-slate-400">{{ $text }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="mx-auto mt-24 max-w-6xl px-4 sm:px-6">
        <h2 class="text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $t['steps_title'] }}</h2>
        <ol class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ($t['steps'] as $i => [$title, $text])
                <li class="rounded-2xl border border-white/10 bg-[#0e1726] p-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-sky-400 text-sm font-bold text-slate-950">{{ $i + 1 }}</span>
                    <h3 class="mt-4 font-semibold text-white">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
        <p class="mt-6 flex items-start gap-2 text-sm text-slate-500">
            <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0" /> {{ $t['note'] }}
        </p>
        <div class="mt-10">
            <x-button :href="url($lang . '/docs')">{{ $t['cta'] }} <x-icon name="arrow" class="h-4 w-4" /></x-button>
        </div>
    </section>
@endsection
