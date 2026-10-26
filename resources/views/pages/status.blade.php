@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key, array $replace = []) => trans('site.status.' . $key, $replace, $lang);
    $status = $status ?? ['state' => 'not_configured'];
    $data = $status['data'] ?? null;
    $tone = [
        'operational' => 'ss-tone-ok', 'degraded' => 'ss-tone-warn', 'partial_outage' => 'ss-tone-warn',
        'major_outage' => 'ss-tone-bad', 'maintenance' => 'ss-tone-info', 'unknown' => 'ss-tone-muted',
    ];
    $bar = fn (?float $value) => match (true) {
        $value === null => 'bg-white/10',
        $value >= 99.5 => 'bg-emerald-400/80',
        $value >= 95 => 'bg-amber-400/80',
        default => 'bg-rose-400/80',
    };
    $time = fn (?string $value) => $value ? \Illuminate\Support\Carbon::parse($value)->setTimezone('Europe/Amsterdam')->locale($lang)->translatedFormat('j F Y, H:i') : null;
@endphp

@section('title', $t('title'))
@section('description', $t('description'))

@section('content')
    <section class="ss-container max-w-4xl pt-16 sm:pt-20">
        <x-page-header :eyebrow="$t('eyebrow')" :title="$t('headline')" :intro="$t('intro')" />

        @if ($status['state'] === 'ok' && $data)
            <div role="status" class="ss-panel mt-10 flex flex-wrap items-center justify-between gap-4 p-5 sm:p-6">
                <p class="flex items-center gap-3 text-lg font-semibold text-white">
                    <span class="ss-badge ss-badge-dot {{ $tone[$data['overall']] }} !px-2.5 !text-sm">{{ $t('states')[$data['overall']] }}</span>
                    {{ $t('overall')[$data['overall']] }}
                </p>
                @if ($data['generated_at'])
                    <p class="font-mono text-xs text-slate-500">{{ $t('updated', ['time' => $time($data['generated_at'])]) }}</p>
                @endif
            </div>

            @if ($data['components'] !== [])
                <h2 class="mt-12 text-lg font-semibold text-white">{{ $t('components') }}</h2>
                <ul class="ss-panel mt-4 divide-y divide-white/[.06]">
                    @foreach ($data['components'] as $component)
                        <li class="p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-medium text-white">{{ $component['name'] }}</p>
                                    @if ($component['description'])<p class="mt-0.5 text-sm text-slate-500">{{ $component['description'] }}</p>@endif
                                </div>
                                <span class="ss-badge ss-badge-dot {{ $tone[$component['state']] }}">{{ $t('states')[$component['state']] }}</span>
                            </div>
                            @if (! empty($component['uptime']))
                                <p class="sr-only">{{ $t('uptime') }}</p>
                                <div class="mt-4 flex h-8 gap-[2px]" aria-hidden="true">
                                    @foreach ($component['uptime'] as $day)
                                        <span class="flex-1 rounded-[2px] {{ $bar($day['uptime']) }}" title="{{ $t('uptime_day', ['date' => $day['date'], 'value' => $day['uptime'] === null ? $t('no_data') : number_format($day['uptime'], 2) . '%']) }}"></span>
                                    @endforeach
                                </div>
                                <p class="mt-2 flex justify-between font-mono text-[11px] text-slate-600"><span>{{ $component['uptime'][0]['date'] }}</span><span>{{ end($component['uptime'])['date'] }}</span></p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif

            <h2 class="mt-12 text-lg font-semibold text-white">{{ $t('incidents') }}</h2>
            @if ($data['incidents'] === [])
                <p class="mt-4 rounded-2xl border border-dashed border-white/10 p-6 text-center text-slate-500">{{ $t('none') }}</p>
            @else
                <ul class="mt-4 grid gap-3">
                    @foreach ($data['incidents'] as $incident)
                        <li class="ss-panel p-5">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="ss-badge {{ $incident['status'] === 'resolved' ? 'ss-tone-ok' : ($incident['severity'] === 'critical' ? 'ss-tone-bad' : 'ss-tone-warn') }}">{{ $t('incident_states')[$incident['status']] }}</span>
                                <span class="ss-badge ss-tone-muted">{{ $t('severity')[$incident['severity']] }}</span>
                            </div>
                            <p class="mt-3 font-medium text-white">{{ $incident['title'] }}</p>
                            <p class="mt-1 text-sm text-slate-500">
                                @if ($incident['component']){{ $incident['component'] }} · @endif
                                {{ $t('started') }} {{ $time($incident['started_at']) }}
                                @if ($incident['resolved_at']) · {{ $t('resolved_at') }} {{ $time($incident['resolved_at']) }}@endif
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif

            <x-button :href="$status['page_url']" variant="secondary" class="mt-8">{{ $t('open_page') }} <x-ss-icon name="external" class="h-4 w-4" /></x-button>
        @elseif ($status['state'] === 'unavailable')
            <div role="alert" class="mt-10 rounded-2xl border border-amber-400/25 bg-amber-400/[.06] p-6">
                <p class="flex items-center gap-2 font-semibold text-amber-200"><x-ss-icon name="alert" class="h-5 w-5" /> {{ $t('unavailable_title') }}</p>
                <p class="mt-2 text-[15px] leading-relaxed text-slate-300">{{ $t('unavailable_text') }}</p>
                <x-button :href="$status['page_url']" variant="secondary" size="sm" class="mt-5">{{ $t('open_page') }} <x-ss-icon name="external" class="h-4 w-4" /></x-button>
            </div>
        @else
            <x-empty-state icon="status" :title="$t('not_configured_title')" class="mt-10">{{ $t('not_configured_text') }}</x-empty-state>
        @endif

        <div class="ss-card mt-12 flex flex-col items-start gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="font-semibold text-white">{{ $t('customer_title') }}</p>
                <p class="mt-1 text-sm text-slate-400">{{ $t('customer_text') }}</p>
            </div>
            <x-button :href="url($lang . '/docs/status-pages')" variant="secondary" size="sm" :arrow="true">{{ $t('customer_cta') }}</x-button>
        </div>
    </section>
@endsection
