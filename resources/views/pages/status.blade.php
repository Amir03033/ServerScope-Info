@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $isEn = $lang === 'en';
    $t = $isEn ? [
        'title' => 'Status — ServerScope',
        'headline' => 'Status',
        'intro' => 'The state of ServerScope and its services.',
        'all_ok' => 'All systems operational',
        'note' => 'Example data. This page does not show live service status yet.',
        'services' => [['Website', 'Operational'], ['Dashboard', 'Operational'], ['Agent connection (HTTPS)', 'Operational'], ['Alerts', 'Operational']],
        'history' => 'Incident history',
        'none' => 'No incidents reported.',
    ] : [
        'title' => 'Status — ServerScope',
        'headline' => 'Status',
        'intro' => 'De toestand van ServerScope en de bijbehorende diensten.',
        'all_ok' => 'Alle systemen operationeel',
        'note' => 'Voorbeeldgegevens. Deze pagina toont nog geen live servicestatus.',
        'services' => [['Website', 'Operationeel'], ['Dashboard', 'Operationeel'], ['Agentverbinding (HTTPS)', 'Operationeel'], ['Waarschuwingen', 'Operationeel']],
        'history' => 'Incidentgeschiedenis',
        'none' => 'Geen incidenten gemeld.',
    ];
@endphp

@section('title', $t['title'])

@section('content')
    <section class="mx-auto max-w-3xl px-4 pb-4 pt-20 sm:px-6">
        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $t['headline'] }}</h1>
        <p class="mt-4 text-lg text-slate-400">{{ $t['intro'] }}</p>

        <div class="mt-10 flex items-center gap-3 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-5">
            <span class="relative flex h-3 w-3">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-400"></span>
            </span>
            <p class="font-semibold text-emerald-200">{{ $t['all_ok'] }}</p>
        </div>

        <ul class="mt-6 divide-y divide-white/5 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/50">
            @foreach ($t['services'] as [$name, $state])
                <li class="flex items-center justify-between px-5 py-4">
                    <span class="font-medium text-white">{{ $name }}</span>
                    <span class="flex items-center gap-2 text-sm text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> {{ $state }}
                    </span>
                </li>
            @endforeach
        </ul>
        <p class="mt-3 text-xs text-slate-500">{{ $t['note'] }}</p>

        <h2 class="mt-14 text-xl font-bold text-white">{{ $t['history'] }}</h2>
        <p class="mt-3 rounded-2xl border border-dashed border-white/10 p-6 text-center text-slate-500">{{ $t['none'] }}</p>
    </section>
@endsection
