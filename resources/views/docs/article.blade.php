@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.docs.' . $key, [], $lang);
    $title = data_get($article ?? null, 'title', $t('fallback_title'));
    $body = data_get($article ?? null, 'html', data_get($article ?? null, 'content', ''));
    $others = collect($articles ?? [])->filter(fn ($item) => filled(data_get($item, 'slug')))->values();
    $current = data_get($article ?? null, 'slug');
    $description = data_get($article ?? null, 'description');
    $toc = data_get($article ?? null, 'toc', []);
    $previous = data_get($article ?? null, 'previous');
    $next = data_get($article ?? null, 'next');
    $app = config('serverscope.app_url');
@endphp

@section('title', $title . ' — ServerScope')
@if (filled($description))
    @section('description', \Illuminate\Support\Str::limit(strip_tags((string) $description), 160))
@endif

@section('content')
    <div class="mx-auto grid max-w-7xl gap-10 px-4 pt-12 sm:px-6 lg:grid-cols-[15rem_minmax(0,1fr)] xl:grid-cols-[15rem_minmax(0,1fr)_13rem]">
        {{-- Sidebar --}}
        <aside class="lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto">
            <form method="GET" action="{{ url($lang . '/docs') }}" role="search" class="relative">
                <label for="docs-search" class="sr-only">{{ $t('search_label') }}</label>
                <x-ss-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                <input id="docs-search" type="search" name="q" maxlength="100" placeholder="{{ $t('search_button') }}…"
                       class="w-full rounded-lg border border-white/10 bg-slate-900/60 py-2 pl-9 pr-3 text-sm text-white placeholder:text-slate-500 focus:border-sky-400/50 focus:outline-none focus:ring-2 focus:ring-sky-400/30">
            </form>

            @if ($others->isNotEmpty())
                {{-- Small screens: collapsed so the article comes first. --}}
                <details class="group mt-4 rounded-lg border border-white/10 bg-slate-900/40 lg:hidden">
                    <summary class="flex cursor-pointer list-none items-center justify-between px-3 py-2.5 text-sm font-semibold text-slate-300 [&::-webkit-details-marker]:hidden">
                        {{ $t('guides') }} ({{ $others->count() }})
                        <x-ss-icon name="arrow" class="h-4 w-4 rotate-90 transition group-open:-rotate-90" />
                    </summary>
                    <div class="border-t border-white/10 p-2">@include('docs.partials.guide-list', ['class' => ''])</div>
                </details>
                <nav class="mt-5 hidden lg:block" aria-label="{{ $t('headline') }}">
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $t('guides') }}</p>
                    @include('docs.partials.guide-list', ['class' => 'mt-2'])
                </nav>
            @endif
        </aside>

        {{-- Article --}}
        <article class="min-w-0">
            <nav aria-label="Breadcrumb" class="mb-5 text-sm text-slate-500">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ url($lang) }}" class="hover:text-sky-300">{{ $t('home') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li><a href="{{ url($lang . '/docs') }}" class="hover:text-sky-300">{{ $t('headline') }}</a></li>
                    <li aria-hidden="true">/</li>
                    <li aria-current="page" class="text-slate-300">{{ $title }}</li>
                </ol>
            </nav>

            @if (data_get($article ?? null, 'coming_soon'))
                <p class="mb-4 rounded-lg border border-amber-400/30 bg-amber-400/10 px-4 py-3 text-sm text-amber-100" role="note">
                    <strong>{{ $t('coming_soon') }}</strong> — {{ $t('coming_soon_text') }}
                </p>
            @endif

            <h1 class="text-4xl font-extrabold leading-tight tracking-tight text-white">{{ $title }}</h1>
            @if (filled($description))
                <p class="mt-4 max-w-2xl text-lg text-slate-400">{{ $description }}</p>
            @endif

            <x-prose :html="$body" class="ss-docs mt-8" data-copy-label="{{ $t('copy') }}" data-copied-label="{{ $t('copied') }}" />

            <div class="mt-14 grid gap-4 sm:grid-cols-2">
                <div class="ss-card rounded-2xl p-5">
                    <p class="font-semibold text-white">{{ $t('app_title') }}</p>
                    <p class="mt-1 text-sm text-slate-400">{{ $t('app_text') }}</p>
                    <x-button :href="$app . '/login'" class="mt-4 !px-4 !py-2" data-app-link>{{ trans('site.nav.login', [], $lang) }} <x-ss-icon name="arrow" class="h-4 w-4" /></x-button>
                </div>
                <div class="ss-card rounded-2xl p-5">
                    <p class="font-semibold text-white">{{ $t('help_title') }}</p>
                    <p class="mt-1 text-sm text-slate-400">{{ $t('help_text') }}</p>
                    <x-button :href="url($lang . '/contact')" variant="secondary" class="mt-4 !px-4 !py-2">{{ trans('site.nav.contact', [], $lang) }}</x-button>
                </div>
            </div>

            @if ($previous || $next)
                <nav class="mt-10 grid gap-3 border-t border-white/10 pt-8 sm:grid-cols-2" aria-label="{{ $t('previous') }} / {{ $t('next') }}">
                    @if ($previous)
                        <a href="{{ url($lang . '/docs/' . $previous['slug']) }}" rel="prev" class="ss-card group rounded-2xl p-5">
                            <span class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-slate-500"><x-ss-icon name="arrow" class="h-3.5 w-3.5 rotate-180" /> {{ $t('previous') }}</span>
                            <span class="mt-1 block font-semibold text-white group-hover:text-sky-300">{{ $previous['title'] }}</span>
                        </a>
                    @else
                        <span class="hidden sm:block"></span>
                    @endif
                    @if ($next)
                        <a href="{{ url($lang . '/docs/' . $next['slug']) }}" rel="next" class="ss-card group rounded-2xl p-5 text-right">
                            <span class="flex items-center justify-end gap-1.5 text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $t('next') }} <x-ss-icon name="arrow" class="h-3.5 w-3.5" /></span>
                            <span class="mt-1 block font-semibold text-white group-hover:text-sky-300">{{ $next['title'] }}</span>
                        </a>
                    @endif
                </nav>
            @endif
        </article>

        {{-- On this page --}}
        @if (count($toc) > 1)
            <aside class="hidden xl:sticky xl:top-24 xl:block xl:h-fit">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $t('on_this_page') }}</p>
                <ul class="mt-3 space-y-2 border-l border-white/10 text-sm">
                    @foreach ($toc as $entry)
                        <li><a href="#{{ $entry['id'] }}" class="-ml-px block border-l border-transparent pl-3 text-slate-400 hover:border-sky-400 hover:text-white">{{ $entry['title'] }}</a></li>
                    @endforeach
                </ul>
            </aside>
        @endif
    </div>
@endsection
