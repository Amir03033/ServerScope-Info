@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.home.' . $key, [], $lang);
    $app = config('serverscope.app_url');
    $doc = fn (string $slug) => url($lang . '/docs/' . $slug);
    $m = $t('mock');
    $guides = $guides ?? [];
    $releases = $releases ?? [];
    $posts = $posts ?? [];
    $categories = trans('site.docs.categories', [], $lang);
    $badges = trans('site.changelog.badges', [], $lang);
    $date = fn (string $value) => \Illuminate\Support\Carbon::parse($value)->locale($lang)->translatedFormat('j F Y');

    // Illustrative sample series for the preview (the page says it is not live data).
    $cpu = 'M0,62 C18,60 30,48 48,52 C66,56 78,30 96,34 C114,38 126,58 144,54 C162,50 174,22 192,26 C210,30 222,44 240,40 C258,36 270,48 288,44 C306,40 318,30 336,32 C354,34 366,40 384,38 C402,36 414,28 432,30';
    $apiBase = $app . '/api/v1';
@endphp

@section('title', $t('title'))
@section('description', $t('description'))

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => [
                ['@type' => 'WebSite', 'name' => 'ServerScope', 'url' => url($lang), 'inLanguage' => $lang],
                ['@type' => 'SoftwareApplication', 'name' => 'ServerScope', 'applicationCategory' => 'DeveloperApplication', 'operatingSystem' => 'Web', 'url' => $app, 'description' => $t('description')],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
    {{-- Hero ------------------------------------------------------------ --}}
    <section class="relative overflow-hidden" aria-labelledby="hero-title">
        <div class="ss-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="ss-glow pointer-events-none absolute inset-x-0 top-0 h-[40rem]" aria-hidden="true"></div>

        <div class="ss-container relative pb-14 pt-16 text-center sm:pt-24">
            <p class="ss-rise inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/[.04] px-3 py-1 font-mono text-[11px] uppercase tracking-wider text-slate-400 sm:text-xs">
                <span class="ss-live-dot text-emerald-400" aria-hidden="true"></span> {{ $t('badge') }}
            </p>

            <h1 id="hero-title" class="ss-rise mx-auto mt-7 max-w-4xl text-[2.6rem] font-semibold leading-[1.04] tracking-[-0.045em] text-white sm:text-6xl lg:text-7xl" style="--rise-delay: 60ms">
                {{ $t('headline') }}
            </h1>
            <p class="ss-lead ss-rise mx-auto mt-6 max-w-2xl" style="--rise-delay: 120ms">{{ $t('intro') }}</p>

            <div class="ss-rise mt-9 flex flex-col justify-center gap-3 sm:flex-row" style="--rise-delay: 180ms">
                <x-button :href="$app . '/login'" size="lg" :arrow="true" data-app-link>{{ $t('primary') }}</x-button>
                <x-button :href="url($lang . '/docs')" variant="secondary" size="lg">{{ $t('secondary') }}</x-button>
            </div>
            <p class="ss-rise mx-auto mt-5 max-w-md text-[13px] text-slate-500" style="--rise-delay: 220ms">{{ $t('access_note') }}</p>
        </div>

        {{-- Product preview: an HTML illustration with example data, clearly labelled. --}}
        <div class="ss-container relative">
            <figure class="ss-rise" style="--rise-delay: 260ms">
                <div class="ss-window p-2 sm:p-3" role="img" aria-label="{{ $t('preview_label') }}">
                    <div class="flex items-center gap-1.5 px-2 pb-2.5 pt-1" aria-hidden="true">
                        <span class="h-2.5 w-2.5 rounded-full bg-white/10"></span><span class="h-2.5 w-2.5 rounded-full bg-white/10"></span><span class="h-2.5 w-2.5 rounded-full bg-white/10"></span>
                        <span class="ml-3 hidden truncate rounded-md bg-white/[.04] px-3 py-1 font-mono text-[11px] text-slate-500 sm:block">{{ preg_replace('~^https?://~', '', $app) }}/dashboard</span>
                    </div>
                    <div class="grid gap-3 rounded-xl bg-ink-950/70 p-3 text-left sm:p-4 lg:grid-cols-[1.6fr_1fr]" aria-hidden="true">
                        <div class="grid gap-3">
                            <div class="grid grid-cols-3 gap-3">
                                @foreach ([[$m['servers'], '12', '12 ' . $m['online'], 'ss-tone-ok'], [$m['domains'], '8', '7 ' . $m['healthy'], 'ss-tone-warn'], [$m['incidents'], '1', $m['acknowledged'], 'ss-tone-info']] as [$label, $value, $sub, $tone])
                                    <div class="rounded-xl border border-white/[.07] bg-white/[.02] p-3 sm:p-4">
                                        <p class="truncate text-[11px] text-slate-500 sm:text-xs">{{ $label }}</p>
                                        <p class="mt-1 text-xl font-semibold tracking-tight text-white sm:text-2xl">{{ $value }}</p>
                                        <span class="ss-badge ss-badge-dot {{ $tone }} mt-2 hidden !text-[10px] sm:inline-flex">{{ $sub }}</span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="rounded-xl border border-white/[.07] bg-white/[.02] p-4">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-white">web-01 · {{ $m['cpu'] }}</p>
                                    <p class="font-mono text-[11px] text-slate-500">{{ $m['last_hour'] }}</p>
                                </div>
                                <svg viewBox="0 0 432 80" preserveAspectRatio="none" class="mt-3 h-28 w-full sm:h-36">
                                    <defs>
                                        <linearGradient id="cpu-fill" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#38bdf8" stop-opacity=".22"/><stop offset="1" stop-color="#38bdf8" stop-opacity="0"/></linearGradient>
                                    </defs>
                                    <g stroke="rgb(148 163 184 / .1)"><path d="M0,20H432M0,40H432M0,60H432"/></g>
                                    <path d="{{ $cpu }} L432,80 L0,80 Z" fill="url(#cpu-fill)"/>
                                    <path d="{{ $cpu }}" pathLength="600" class="ss-draw" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linejoin="round" vector-effect="non-scaling-stroke"/>
                                </svg>
                                <div class="mt-3 grid grid-cols-3 gap-2 font-mono text-[11px] text-slate-500">
                                    <span>{{ $m['cpu'] }} <b class="font-medium text-slate-200">23%</b></span>
                                    <span>{{ $m['memory'] }} <b class="font-medium text-slate-200">61%</b></span>
                                    <span>{{ $m['load'] }} <b class="font-medium text-slate-200">0.42</b></span>
                                </div>
                            </div>
                        </div>
                        <div class="hidden gap-3 sm:grid">
                            <div class="rounded-xl border border-white/[.07] bg-white/[.02] p-4">
                                <p class="text-sm font-medium text-white">shop.example.com</p>
                                <ul class="mt-3 space-y-2 text-xs">
                                    <li class="flex items-center justify-between gap-2"><span class="text-slate-400">HTTP</span><span class="ss-badge ss-badge-dot ss-tone-ok">{{ $m['http'] }}</span></li>
                                    <li class="flex items-center justify-between gap-2"><span class="text-slate-400">{{ $m['cert'] }}</span><span class="ss-badge ss-badge-dot ss-tone-ok">{{ $m['cert_value'] }}</span></li>
                                    <li class="flex items-center justify-between gap-2"><span class="text-slate-400">DNS</span><span class="ss-badge ss-badge-dot ss-tone-ok">{{ $m['ok'] }}</span></li>
                                    <li class="flex items-center justify-between gap-2"><span class="text-slate-400">RDAP</span><span class="ss-badge ss-badge-dot ss-tone-warn">{{ $m['warn'] }} · 14d</span></li>
                                </ul>
                            </div>
                            <div class="rounded-xl border border-amber-400/20 bg-amber-400/[.04] p-4">
                                <div class="flex items-start gap-3">
                                    <x-ss-icon name="alert" class="mt-0.5 h-4 w-4 shrink-0 text-amber-300" />
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-white">{{ $m['incident_title'] }}</p>
                                        <p class="mt-1 font-mono text-[11px] text-slate-500">{{ $m['incident_meta'] }}</p>
                                        <span class="ss-badge ss-tone-info mt-3">{{ $m['acknowledged'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <figcaption class="mt-4 flex items-center justify-center gap-2 text-xs text-slate-500">
                    <x-ss-icon name="info" class="h-3.5 w-3.5" /> {{ $t('illustrative') }}
                </figcaption>
            </figure>
        </div>
    </section>

    {{-- What it monitors ----------------------------------------------- --}}
    @php $overview = $t('overview'); @endphp
    <section class="ss-container mt-32" aria-labelledby="overview-title">
        <x-section-heading id="overview-title" :eyebrow="$overview['eyebrow']" :title="$overview['title']" :text="$overview['text']" data-reveal />

        <ul class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach ($overview['items'] as [$icon, $title, $text, $slug])
                <li data-reveal>
                    <a href="{{ $doc($slug) }}" class="ss-card group flex h-full flex-col p-6">
                        <x-icon-tile :name="$icon" />
                        <h3 class="mt-5 font-semibold text-white">{{ $title }}</h3>
                        <p class="mt-2 flex-1 text-[15px] leading-relaxed text-slate-400">{{ $text }}</p>
                        <x-ss-icon name="arrow" class="mt-5 h-4 w-4 text-slate-600 transition group-hover:translate-x-1 group-hover:text-sky-300" />
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- How it works ---------------------------------------------------- --}}
    @php $steps = $t('steps'); @endphp
    <section class="ss-container mt-32" aria-labelledby="steps-title">
        <x-section-heading id="steps-title" :eyebrow="$steps['eyebrow']" :title="$steps['title']" data-reveal />
        <div class="relative mt-12">
        <div class="ss-hairline pointer-events-none absolute left-[16%] right-[16%] top-[2.35rem] hidden md:block" aria-hidden="true"></div>
        <ol class="relative grid gap-4 md:grid-cols-3" data-reveal-group>
            @foreach ($steps['items'] as $i => [$title, $text])
                <li data-reveal class="ss-panel relative p-6">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full border border-sky-400/40 bg-ink-900 font-mono text-sm font-semibold text-sky-300">{{ str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) }}</span>
                    <h3 class="mt-5 font-semibold text-white">{{ $title }}</h3>
                    <p class="mt-2 text-[15px] leading-relaxed text-slate-400">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
        </div>
    </section>

    {{-- Websites, SSL and domains --------------------------------------- --}}
    @php $domains = $t('domains'); @endphp
    <section class="ss-container mt-32 grid items-center gap-12 lg:grid-cols-2" aria-labelledby="domains-title">
        <div data-reveal>
            <x-section-heading id="domains-title" :eyebrow="$domains['eyebrow']" :title="$domains['title']" :text="$domains['text']" />
            <ul class="mt-8 space-y-3">
                @foreach ($domains['points'] as $point)
                    <li class="flex gap-3 text-[15px] text-slate-300"><x-ss-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" /> {{ $point }}</li>
                @endforeach
            </ul>
            <x-button :href="$doc('domain-monitoring')" variant="secondary" class="mt-8" :arrow="true">{{ $domains['cta'] }}</x-button>
        </div>
        <div data-reveal class="ss-window p-2" aria-hidden="true">
            <div class="rounded-xl bg-ink-950/70 p-4 sm:p-5">
                @foreach ([['api.example.com', 'ok', 'HTTP 200 · 96 ms', $m['cert_value']], ['shop.example.com', 'ok', 'HTTP 200 · 182 ms', $m['cert_value']], ['old.example.org', 'warn', 'HTTP 200 · 341 ms', 'RDAP · 14d']] as [$name, $tone, $http, $cert])
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/[.06] py-3.5 last:border-0">
                        <div class="flex min-w-0 items-center gap-3">
                            <x-ss-icon name="globe" class="h-4 w-4 shrink-0 text-slate-500" />
                            <span class="truncate font-mono text-[13px] text-slate-200">{{ $name }}</span>
                        </div>
                        <div class="flex flex-wrap gap-1.5">
                            <span class="ss-badge ss-badge-dot ss-tone-ok">{{ $http }}</span>
                            <span class="ss-badge ss-badge-dot {{ $tone === 'ok' ? 'ss-tone-ok' : 'ss-tone-warn' }}">{{ $cert }}</span>
                        </div>
                    </div>
                @endforeach
                <p class="mt-3 text-[11px] text-slate-600">{{ $t('illustrative') }}</p>
            </div>
        </div>
    </section>

    {{-- Incidents ------------------------------------------------------- --}}
    @php $incidents = $t('incidents'); @endphp
    <section class="ss-container mt-32 grid items-center gap-12 lg:grid-cols-2" aria-labelledby="incidents-title">
        <div data-reveal class="lg:order-2">
            <x-section-heading id="incidents-title" :eyebrow="$incidents['eyebrow']" :title="$incidents['title']" :text="$incidents['text']" />
            <ul class="mt-8 space-y-3">
                @foreach ($incidents['points'] as $point)
                    <li class="flex gap-3 text-[15px] text-slate-300"><x-ss-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" /> {{ $point }}</li>
                @endforeach
            </ul>
            <x-button :href="$doc('alerts-incidents')" variant="secondary" class="mt-8" :arrow="true">{{ $incidents['cta'] }}</x-button>
        </div>
        <div data-reveal class="ss-panel p-5 sm:p-6 lg:order-1" aria-hidden="true">
            <ol class="relative space-y-5 border-l border-white/10 pl-6">
                @foreach ($incidents['timeline'] as $i => [$time, $title, $text])
                    <li class="relative">
                        <span class="absolute -left-[1.92rem] top-1 flex h-3 w-3 rounded-full border-2 border-ink-850 {{ $i === 0 ? 'bg-rose-400' : ($loop->last ? 'bg-emerald-400' : 'bg-sky-400') }}"></span>
                        <p class="font-mono text-[11px] text-slate-500">{{ $time }}</p>
                        <p class="mt-0.5 text-sm font-medium text-white">{{ $title }}</p>
                        <p class="text-sm text-slate-400">{{ $text }}</p>
                    </li>
                @endforeach
            </ol>
            <p class="mt-5 text-[11px] text-slate-600">{{ $t('illustrative') }}</p>
        </div>
    </section>

    {{-- Notifications and integrations ---------------------------------- --}}
    @php $integrations = $t('integrations'); @endphp
    <section class="ss-container mt-32" aria-labelledby="integrations-title">
        <x-section-heading id="integrations-title" :eyebrow="$integrations['eyebrow']" :title="$integrations['title']" :text="$integrations['text']" data-reveal />
        <ul class="mt-12 grid gap-px overflow-hidden rounded-2xl border border-white/[.08] bg-white/[.06] sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
            @foreach ($integrations['items'] as [$icon, $title, $text, $slug])
                <li data-reveal class="bg-ink-900">
                    <a href="{{ $doc($slug) }}" class="group flex h-full gap-4 p-6 transition hover:bg-white/[.025]">
                        <x-ss-icon :name="$icon" class="mt-0.5 h-5 w-5 shrink-0 text-sky-300" />
                        <span>
                            <span class="flex items-center gap-1.5 font-semibold text-white">{{ $title }} <x-ss-icon name="chevron" class="h-3.5 w-3.5 text-slate-600 transition group-hover:translate-x-0.5 group-hover:text-sky-300" /></span>
                            <span class="mt-1.5 block text-[15px] leading-relaxed text-slate-400">{{ $text }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Developers ------------------------------------------------------ --}}
    @php $dev = $t('developers'); @endphp
    <section class="ss-container mt-32 grid items-center gap-12 lg:grid-cols-[1fr_1.15fr]" aria-labelledby="developers-title">
        <div data-reveal class="min-w-0">
            <x-section-heading id="developers-title" :eyebrow="$dev['eyebrow']" :title="$dev['title']" :text="$dev['text']" />
            <ul class="mt-8 space-y-3">
                @foreach ($dev['points'] as $point)
                    <li class="flex gap-3 text-[15px] text-slate-300"><x-ss-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-400" /> {{ $point }}</li>
                @endforeach
            </ul>
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button :href="$doc('api-reference')" :arrow="true">{{ $dev['cta'] }}</x-button>
                <x-button :href="$doc('webhooks')" variant="secondary">{{ $dev['secondary'] }}</x-button>
            </div>
        </div>
        {{-- Real request from the API reference; tabs are added by app.js. --}}
        <div data-reveal class="ss-prose min-w-0 !max-w-none" data-code-blocks data-copy-label="{{ trans('site.docs.copy', [], $lang) }}" data-copied-label="{{ trans('site.docs.copied', [], $lang) }}" data-tabs-label="{{ trans('site.docs.tabs_label', [], $lang) }}">
<pre><code class="language-curl">curl -s "{{ $apiBase }}/incidents?state=active" \
  -H "Authorization: Bearer $SERVERSCOPE_API_TOKEN" \
  -H "Accept: application/json"</code></pre><pre><code class="language-php">$response = Http::withToken(env('SERVERSCOPE_API_TOKEN'))
    ->acceptJson()
    ->get('{{ $apiBase }}/incidents', ['state' => 'active']);

foreach ($response->throw()->json('data') as $incident) {
    echo "{$incident['severity']}: {$incident['title']}\n";
}</code></pre><pre><code class="language-javascript">const response = await fetch('{{ $apiBase }}/incidents?state=active', {
  headers: {
    Authorization: `Bearer ${process.env.SERVERSCOPE_API_TOKEN}`,
    Accept: 'application/json',
  },
});
const { data } = await response.json();</code></pre>
        </div>
    </section>

    {{-- Documentation preview ------------------------------------------- --}}
    @if ($guides !== [])
        @php $docsCopy = $t('docs'); @endphp
        <section class="ss-container mt-32" aria-labelledby="docs-title">
            <x-section-heading id="docs-title" :eyebrow="$docsCopy['eyebrow']" :title="$docsCopy['title']" :text="$docsCopy['text']" data-reveal>
                <x-slot:action><x-button :href="url($lang . '/docs')" variant="secondary" :arrow="true">{{ $docsCopy['cta'] }}</x-button></x-slot:action>
            </x-section-heading>
            <ul class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                @foreach ($guides as $guide)
                    <li data-reveal>
                        <a href="{{ $doc($guide['slug']) }}" class="ss-card group flex h-full items-start gap-4 p-5">
                            <x-icon-tile :name="$guide['icon']" size="sm" />
                            <span class="min-w-0">
                                <span class="block font-mono text-[11px] uppercase tracking-wider text-slate-500">{{ $categories[$guide['category']] ?? '' }}</span>
                                <span class="mt-1 block font-semibold text-white group-hover:text-sky-200">{{ $guide['title'] }}</span>
                                <span class="mt-1 line-clamp-2 block text-sm leading-relaxed text-slate-400">{{ $guide['description'] }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    {{-- Changelog and blog ---------------------------------------------- --}}
    @if ($releases !== [] || $posts !== [])
        <section class="ss-container mt-32 grid gap-10 {{ $releases !== [] && $posts !== [] ? 'lg:grid-cols-2' : '' }}">
            @if ($releases !== [])
                @php $cl = $t('changelog'); @endphp
                <section data-reveal aria-labelledby="changelog-title">
                    <div class="flex items-end justify-between gap-4">
                        <div><p class="ss-eyebrow">{{ $cl['eyebrow'] }}</p><h2 id="changelog-title" class="mt-3 text-2xl font-semibold tracking-tight text-white">{{ $cl['title'] }}</h2></div>
                        <a href="{{ url($lang . '/changelog') }}" class="inline-flex items-center gap-1 text-sm font-medium text-sky-300 hover:text-sky-200">{{ $cl['cta'] }} <x-ss-icon name="chevron" class="h-4 w-4" /></a>
                    </div>
                    <ul class="mt-6 space-y-3">
                        @foreach ($releases as $release)
                            <li>
                                <a href="{{ url($lang . '/changelog') }}#{{ $release['anchor'] }}" class="ss-card block p-5">
                                    <span class="flex flex-wrap items-baseline gap-x-3"><span class="font-mono font-semibold text-white">v{{ $release['version'] }}</span><time class="text-sm text-slate-500" datetime="{{ $release['date'] }}">{{ $date($release['date']) }}</time></span>
                                    <ul class="mt-3 space-y-1.5">
                                        @foreach (array_slice($release['items'], 0, 3) as [$type, $text])
                                            <li class="flex items-start gap-2.5 text-sm text-slate-400"><span class="mt-0.5 shrink-0 font-mono text-[10px] uppercase tracking-wider text-slate-500">{{ $badges[$type] ?? $type }}</span> <span>{{ $text }}</span></li>
                                        @endforeach
                                    </ul>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
            @if ($posts !== [])
                @php $bl = $t('blog'); @endphp
                <section data-reveal aria-labelledby="blog-title">
                    <div class="flex items-end justify-between gap-4">
                        <div><p class="ss-eyebrow">{{ $bl['eyebrow'] }}</p><h2 id="blog-title" class="mt-3 text-2xl font-semibold tracking-tight text-white">{{ $bl['title'] }}</h2></div>
                        <a href="{{ url($lang . '/blog') }}" class="inline-flex items-center gap-1 text-sm font-medium text-sky-300 hover:text-sky-200">{{ $bl['cta'] }} <x-ss-icon name="chevron" class="h-4 w-4" /></a>
                    </div>
                    <ul class="mt-6 space-y-3">
                        @foreach ($posts as $post)
                            <li>
                                <a href="{{ url($lang . '/blog/' . $post['slug']) }}" class="ss-card block p-5">
                                    <time class="font-mono text-[11px] uppercase tracking-wider text-slate-500" datetime="{{ $post['date'] }}">{{ $date($post['date']) }}</time>
                                    <span class="mt-1.5 block font-semibold text-white">{{ $post['title'] }}</span>
                                    @if ($post['excerpt'])<span class="mt-1 line-clamp-2 block text-sm text-slate-400">{{ $post['excerpt'] }}</span>@endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </section>
    @endif

    {{-- Final call to action -------------------------------------------- --}}
    @php $cta = $t('cta'); @endphp
    <section class="ss-container mt-32">
        <div data-reveal class="ss-cta flex flex-col items-start justify-between gap-8 p-8 sm:p-12 lg:flex-row lg:items-center">
            <div class="max-w-xl">
                <h2 class="ss-h2">{{ $cta['title'] }}</h2>
                <p class="ss-lead mt-3">{{ $cta['text'] }}</p>
            </div>
            <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <x-button :href="$app . '/login'" size="lg" :arrow="true" data-app-link>{{ $cta['primary'] }}</x-button>
                <x-button :href="$doc('getting-started')" variant="secondary" size="lg">{{ $cta['secondary'] }}</x-button>
            </div>
        </div>
    </section>
@endsection
