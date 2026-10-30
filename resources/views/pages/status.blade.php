@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.status.' . $key, [], $lang);
    $s = fn (string $key) => trans('site.status2.' . $key, [], $lang);
    $service = app(\App\Services\Site\StatusService::class);
    $status = $status ?? ['state' => 'unknown', 'components' => [], 'incidents' => [], 'generated_at' => null];
    $pageUrl = $pageUrl ?? $service->pageUrl();
    $state = $status['state'];
    $dot = fn (string $state) => match ($state) {
        'operational' => 'ss-dot-ok',
        'degraded', 'maintenance', 'partial_outage' => 'ss-dot-warn',
        'major_outage' => 'ss-dot-bad',
        default => '',
    };
@endphp

@section('title', \App\Support\Seo::title('status', $lang))
@section('description', \App\Support\Seo::description('status', $lang))
@if (\App\Support\Seo::noindex('status', $lang))
    @section('noindex', '1')
@endif

@section('content')
    <section class="ss-container max-w-3xl pt-16 sm:pt-24">
        <x-page-header :title="$t('headline')" :intro="$t('intro')" />

        <div class="ss-card mt-10 flex flex-wrap items-center gap-4 p-6" role="status">
            <span class="ss-dot {{ $dot($state) }} {{ $state === 'operational' ? 'ss-live-dot' : '' }} !h-3 !w-3" aria-hidden="true"></span>
            <div class="min-w-0 flex-1">
                <p class="text-lg font-semibold text-fg">{{ $s($state) }}</p>
                @if ($state === 'unknown')
                    <p class="mt-1 text-sm text-muted">{{ $s('unknown_text') }}</p>
                @elseif ($status['generated_at'])
                    <p class="mt-1 text-sm text-muted">{{ $s('updated') }}: <time datetime="{{ $status['generated_at'] }}">{{ \Illuminate\Support\Carbon::parse($status['generated_at'])->timezone(config('app.timezone'))->locale($lang)->translatedFormat('j F Y, H:i') }}</time></p>
                @endif
            </div>
            @if (filled($pageUrl))
                <x-button :href="$pageUrl" variant="secondary" rel="noopener">{{ $s('open') }} <x-ss-icon name="external" class="h-4 w-4" /></x-button>
            @endif
        </div>

        @if ($status['components'] !== [])
            <h2 class="mt-12 text-sm font-semibold uppercase tracking-[.08em] text-subtle">{{ $s('components') }}</h2>
            <ul class="mt-4 divide-y divide-line overflow-hidden rounded-2xl border border-line bg-surface">
                @foreach ($status['components'] as $component)
                    <li class="flex items-center justify-between gap-4 px-5 py-4">
                        <span class="font-medium text-fg">{{ $component['name'] }}</span>
                        <span class="flex items-center gap-2 text-sm text-muted">
                            <span class="ss-dot {{ $dot($component['state']) }}" aria-hidden="true"></span>
                            {{ $component['state'] === 'operational' ? $s('state_operational') : $s($component['state']) }}
                        </span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($state !== 'unknown')
            <h2 class="mt-12 text-sm font-semibold uppercase tracking-[.08em] text-subtle">{{ $s('incidents') }}</h2>
            @if ($status['incidents'] === [])
                <p class="mt-4 text-muted">{{ $s('no_incidents') }}</p>
            @else
                <ul class="mt-4 space-y-3">
                    @foreach ($status['incidents'] as $incident)
                        <li class="ss-card p-5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <p class="font-medium text-fg">{{ $incident['title'] ?? $incident['component'] ?? '—' }}</p>
                                <span class="ss-badge"><span class="ss-dot {{ $incident['resolved'] ? 'ss-dot-ok' : 'ss-dot-warn' }}" aria-hidden="true"></span>{{ $incident['resolved'] ? $s('resolved') : $s('ongoing') }}</span>
                            </div>
                            @if ($incident['started_at'])
                                <p class="mt-1 text-sm text-muted"><time datetime="{{ $incident['started_at'] }}">{{ \Illuminate\Support\Carbon::parse($incident['started_at'])->timezone(config('app.timezone'))->locale($lang)->translatedFormat('j F Y, H:i') }}</time></p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        @endif

        <p class="mt-10 text-xs text-subtle">{{ $s('source') }}</p>
    </section>
@endsection
