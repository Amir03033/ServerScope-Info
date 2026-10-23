@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $isEn = $lang === 'en';
    $t = $isEn ? [
        'title' => 'Updates — ServerScope',
        'headline' => 'Updates',
        'intro' => 'Changes, new features and improvements, newest first.',
        'badges' => ['new' => 'New', 'improved' => 'Improved', 'fixed' => 'Fixed'],
    ] : [
        'title' => 'Updates — ServerScope',
        'headline' => 'Updates',
        'intro' => 'Wijzigingen, nieuwe functies en verbeteringen, nieuwste bovenaan.',
        'badges' => ['new' => 'Nieuw', 'improved' => 'Verbeterd', 'fixed' => 'Opgelost'],
    ];

    // Vul deze lijst aan met je eigen releases (nieuwste bovenaan).
    $entries = $entries ?? [
        [
            'version' => '0.1.0',
            'date' => '2026-09-24',
            'items' => $isEn
                ? [['new', 'First version of the dashboard with CPU, memory, load average, disk and network.'],
                   ['new', 'Agent connection over HTTPS.'],
                   ['new', 'Dutch and English website.']]
                : [['new', 'Eerste versie van het dashboard met CPU, geheugen, load average, schijf en netwerk.'],
                   ['new', 'Agentverbinding via HTTPS.'],
                   ['new', 'Nederlandse en Engelse website.']],
        ],
    ];
    $badge = [
        'new' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
        'improved' => 'border-sky-400/20 bg-sky-400/10 text-sky-300',
        'fixed' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
    ];
@endphp

@section('title', $t['title'])

@section('content')
    <section class="mx-auto max-w-3xl px-4 pt-20 sm:px-6">
        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $t['headline'] }}</h1>
        <p class="mt-4 text-lg text-slate-400">{{ $t['intro'] }}</p>

        <ol class="relative mt-14 space-y-10 border-l border-white/10 pl-8">
            @foreach ($entries as $entry)
                <li class="relative">
                    <span class="absolute -left-[2.45rem] top-1.5 h-3 w-3 rounded-full border-2 border-[#0b1220] bg-sky-400"></span>
                    <div class="flex flex-wrap items-baseline gap-x-3">
                        <h2 class="text-xl font-bold text-white">v{{ $entry['version'] }}</h2>
                        <time class="text-sm text-slate-500" datetime="{{ $entry['date'] }}">{{ \Illuminate\Support\Carbon::parse($entry['date'])->locale($lang)->translatedFormat('j F Y') }}</time>
                    </div>
                    <ul class="mt-4 space-y-3">
                        @foreach ($entry['items'] as [$type, $text])
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 shrink-0 rounded-md border px-2 py-0.5 text-xs font-semibold {{ $badge[$type] ?? $badge['new'] }}">{{ $t['badges'][$type] ?? $type }}</span>
                                <span class="text-slate-300">{{ $text }}</span>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ol>
    </section>
@endsection
