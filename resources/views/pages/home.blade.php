@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.home.' . $key, [], $lang);
    $c = fn (string $key) => trans('site.common.' . $key, [], $lang);
    $site = app(\App\Services\Site\SiteData::class);
    $counts = $site->counts($lang);
    $features = $site->featuresByCategory();
    $install = $site->featureStatus('one-command-install');
    $installCommand = "curl --proto '=https' --tlsv1.2 -fsSL " . config('serverscope.app_url') . '/install.sh -o serverscope-install.sh && sudo bash serverscope-install.sh';
    $categories = \App\Services\Site\FeatureCatalog::CATEGORIES;
    $steps = trans('site.features.steps', [], $lang);

    // Illustrative sample data for the preview (labelled as an example on the page).
    $kpis = [
        [$t('cpu'), '0.7%', 'M0,34 L20,32 L40,35 L55,12 L65,33 L85,30 L110,34 L140,33 L160,36 L200,34', 'cpu'],
        [$t('memory'), '38.6%', 'M0,28 L25,22 L45,30 L70,14 L95,26 L120,20 L150,24 L180,16 L200,22', 'memory'],
        [$t('load'), '0.00', 'M0,36 L30,35 L55,10 L70,34 L110,33 L135,12 L150,34 L200,30', 'chart'],
        [$t('uptime'), $lang === 'en' ? '20d 10h' : '20d 10u', 'M0,30 L200,30', 'clock'],
    ];
    $chart = 'M0,110 C30,100 50,60 80,70 C110,80 130,30 160,40 C190,50 210,90 240,70 C270,50 300,20 330,30 C350,36 370,50 400,40';
@endphp

@section('title', $t('title'))
@section('description', $t('description'))

@section('content')
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-line">
        <div class="ss-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
        {{-- The subtle moving element: a slow heartbeat tracing along the bottom. --}}
        <svg class="pointer-events-none absolute inset-x-0 bottom-0 h-16 w-full lg:bottom-6 lg:h-24" viewBox="0 0 1200 100" preserveAspectRatio="none" aria-hidden="true">
            <path class="ss-pulse-line-base" d="M0,60 H420 L445,60 L460,20 L480,92 L500,40 L512,60 H1200" fill="none" stroke-width="1" vector-effect="non-scaling-stroke"/>
            <path class="ss-pulse-line" d="M0,60 H420 L445,60 L460,20 L480,92 L500,40 L512,60 H1200" fill="none" stroke-width="1.5" vector-effect="non-scaling-stroke"/>
        </svg>

        <div class="ss-container relative grid items-center gap-12 pb-28 pt-16 sm:pt-24 lg:grid-cols-[1.1fr_1fr] lg:pb-32">
            <div>
                <x-status-badge :locale="$lang" class="ss-rise" />
                <h1 class="ss-rise ss-rise-2 mt-6 max-w-xl text-[2.5rem] font-semibold leading-[1.05] tracking-[-0.035em] text-fg sm:text-6xl">{{ $t('v2_headline') }}</h1>
                <p class="ss-rise ss-rise-3 mt-6 max-w-xl text-lg leading-relaxed text-muted">{{ $t('v2_intro') }}</p>
                <div class="ss-rise ss-rise-4 mt-9 flex flex-wrap gap-3">
                    <x-button :href="url($lang . '/features')" class="!px-5 !py-2.5">{{ $t('v2_primary') }} <x-ss-icon name="arrow" class="ss-arrow h-4 w-4" /></x-button>
                    <x-button :href="url($lang . '/docs')" variant="secondary" class="!px-5 !py-2.5">{{ $t('v2_secondary') }}</x-button>
                </div>
            </div>

            @if ($install?->isVisible())
                <div class="ss-rise ss-rise-3 min-w-0">
                    <div class="mb-3 flex items-center gap-2">
                        <p class="text-sm font-semibold text-fg">{{ $t('terminal_title') }}</p>
                        @if ($install === \App\Enums\FeatureStatus::ComingSoon)
                            <span class="ss-badge ss-badge-soon">{{ $c('coming_soon') }}</span>
                        @endif
                    </div>
                    <x-terminal :command="$installCommand" title="serverscope-install.sh" :label="$t('terminal_label')"
                                :copy-label="trans('site.docs.copy', [], $lang)" :copied-label="trans('site.docs.copied', [], $lang)" />
                    <ul class="mt-4 space-y-1.5 text-sm text-muted">
                        @foreach ($t('terminal_points') as $point)
                            <li class="flex gap-2"><x-ss-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-accent" /> {{ $point }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </section>

    {{-- Real numbers only --}}
    @php
        $numbers = array_filter([
            [$counts['features'], $t('count_features')],
            [$counts['docs'], $t('count_docs')],
            [2, $t('count_distros')],
            [2, $t('count_arch')],
        ], fn ($n) => $n[0] !== null);
    @endphp
    <section class="ss-container mt-16" aria-label="{{ $t('counters_title') }}">
        <dl class="grid grid-cols-2 divide-line border-y border-line md:grid-cols-4 md:divide-x" data-reveal-group>
            @foreach ($numbers as [$value, $label])
                <div data-reveal class="px-4 py-7 md:px-6">
                    <dt class="text-sm text-muted">{{ $label }}</dt>
                    <dd class="mt-1 text-4xl font-semibold tabular-nums tracking-tight text-fg" data-count="{{ $value }}">{{ $value }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Features from the admin panel --}}
    @if ($features->isNotEmpty())
        <section class="ss-container mt-28">
            <div class="max-w-2xl">
                <h2 data-reveal class="text-3xl font-semibold tracking-[-0.025em] text-fg sm:text-4xl">{{ $t('features_title') }}</h2>
                <p data-reveal class="mt-4 text-lg text-muted">{{ $t('features_text') }}</p>
            </div>

            <div class="mt-12 grid gap-px overflow-hidden rounded-2xl border border-line bg-line sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                @foreach ($features->flatten()->take(9) as $feature)
                    <article data-reveal class="group bg-surface p-6 transition-colors hover:bg-surface-2">
                        <div class="flex items-center justify-between gap-3">
                            <x-icon-tile :name="$feature->icon" size="sm" />
                            @if ($feature->isComingSoon())
                                <span class="ss-badge ss-badge-soon">{{ $c('coming_soon') }}</span>
                            @endif
                        </div>
                        <h3 class="mt-5 font-semibold text-fg">{{ $feature->translate('name', $lang) }}</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $feature->translate('summary', $lang) }}</p>
                    </article>
                @endforeach
            </div>
            <p class="mt-6"><a href="{{ url($lang . '/features') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-accent hover:text-accent-hover">{{ $t('features_all') }} <x-ss-icon name="arrow" class="h-4 w-4" /></a></p>
        </section>
    @endif

    {{-- How it works --}}
    <section class="ss-fade-top mt-28 py-24">
        <div class="ss-container">
            <h2 data-reveal class="text-3xl font-semibold tracking-[-0.025em] text-fg sm:text-4xl">{{ $t('steps_title') }}</h2>
            <div class="relative mt-12">
                <div class="ss-steps-line absolute left-0 right-0 top-5 hidden h-px bg-accent md:block" aria-hidden="true"></div>
                <ol class="relative grid gap-8 md:grid-cols-3" data-reveal-group>
                    @foreach ($steps as $i => [$title, $text])
                        <li data-reveal>
                            <span class="relative flex h-10 w-10 items-center justify-center rounded-full border border-accent bg-bg text-sm font-semibold text-accent">{{ $i + 1 }}</span>
                            <h3 class="mt-5 font-semibold text-fg">{{ $title }}</h3>
                            <p class="mt-2 leading-relaxed text-muted">{{ $text }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    {{-- Example dashboard (fictional data, labelled) --}}
    <section class="ss-container mt-24" aria-labelledby="preview-title">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <h2 id="preview-title" data-reveal class="text-3xl font-semibold tracking-[-0.025em] text-fg sm:text-4xl">{{ $t('preview_title') }}</h2>
            <span class="ss-badge"><span class="ss-dot" aria-hidden="true"></span>{{ $t('sample') }}</span>
        </div>
        <div data-reveal class="mt-8 rounded-2xl border border-line bg-surface-2 p-2 shadow-card sm:p-3" role="img" aria-label="{{ $t('preview') }} — {{ $t('sample') }}">
            <div class="rounded-xl border border-line bg-surface p-4 sm:p-5" aria-hidden="true">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="font-semibold text-fg">Dashboard</p>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="ss-badge"><span class="ss-dot ss-dot-ok"></span>{{ $t('online') }}</span>
                        <span class="ss-badge">{{ $t('range') }}</span>
                    </div>
                </div>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($kpis as [$label, $value, $points, $icon])
                        <div class="rounded-xl border border-line p-4">
                            <div class="flex items-center gap-3">
                                <x-icon-tile :name="$icon" size="sm" />
                                <div class="min-w-0"><p class="truncate text-xs text-muted">{{ $label }}</p><p class="text-xl font-semibold tabular-nums text-fg">{{ $value }}</p></div>
                            </div>
                            <svg viewBox="0 0 200 40" preserveAspectRatio="none" class="mt-3 h-8 w-full text-accent"><path d="{{ $points }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" vector-effect="non-scaling-stroke"/></svg>
                        </div>
                    @endforeach
                </div>
                <div class="mt-3 rounded-xl border border-line p-4">
                    <p class="text-sm font-semibold text-fg">{{ $t('cpu') }}</p>
                    <svg viewBox="0 0 400 130" preserveAspectRatio="none" class="mt-3 h-32 w-full text-accent">
                        <g stroke="var(--ss-line)"><path d="M0,35H400M0,70H400M0,105H400"/></g>
                        <path d="{{ $chart }} L400,130 L0,130 Z" fill="currentColor" fill-opacity=".08"/>
                        <path d="{{ $chart }}" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                    </svg>
                </div>
            </div>
        </div>
        <p class="mt-3 text-center text-xs text-subtle">{{ $t('illustrative') }}</p>
    </section>

    {{-- CTA --}}
    <section class="ss-container mt-28">
        <div data-reveal class="flex flex-col items-start justify-between gap-6 rounded-2xl border border-line bg-surface p-8 shadow-card sm:flex-row sm:items-center sm:p-12">
            <div class="max-w-xl">
                <h2 class="text-2xl font-semibold tracking-[-0.02em] text-fg sm:text-3xl">{{ $t('cta2_title') }}</h2>
                <p class="mt-3 text-muted">{{ $t('cta2_text') }}</p>
            </div>
            <x-button :href="url($lang . '/docs')" class="shrink-0">{{ $t('cta') }} <x-ss-icon name="arrow" class="ss-arrow h-4 w-4" /></x-button>
        </div>
    </section>
@endsection
