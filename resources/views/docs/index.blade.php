@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key, array $replace = []) => trans('site.docs.' . $key, $replace, $lang);
    $articles = collect($articles ?? [])->filter(fn ($article) => filled(data_get($article, 'slug')))->values();
    $groups = $groups ?? ($articles->isNotEmpty() ? ['start' => $articles->all()] : []);
    $labels = $t('categories');
    $query = (string) ($query ?? '');
    $results = $results ?? null;
@endphp

@section('title', $query !== '' ? $t('results') . ' “' . $query . '” — ServerScope' : $t('title'))
@section('description', $t('intro'))
@if ($query !== '')
    {{-- Search result pages are not useful in search engines. --}}
    @section('robots', 'noindex, follow')
@endif

@section('content')
    <section class="relative overflow-hidden">
        <div class="ss-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="ss-glow pointer-events-none absolute inset-x-0 top-0 h-[26rem]" aria-hidden="true"></div>
        <div class="ss-container relative pt-12 sm:pt-16">
            <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
                <ol class="flex flex-wrap items-center gap-1.5">
                    <li><a href="{{ url($lang) }}" class="hover:text-slate-200">{{ $t('home') }}</a></li>
                    <li aria-hidden="true"><x-ss-icon name="chevron" class="h-3.5 w-3.5" /></li>
                    <li aria-current="page" class="text-slate-300">{{ $t('headline') }}</li>
                </ol>
            </nav>

            <x-page-header class="mt-8" :eyebrow="$t('eyebrow')" :title="$t('headline')" :intro="$t('intro')" />

            <form id="search" method="GET" action="{{ url($lang . '/docs') }}" role="search" class="ss-rise mt-8 flex max-w-xl flex-col gap-2 sm:flex-row" style="--rise-delay: 180ms">
                <label for="docs-search" class="sr-only">{{ $t('search_label') }}</label>
                <div class="relative flex-1">
                    <x-ss-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                    <input id="docs-search" type="search" name="q" value="{{ $query }}" maxlength="100" placeholder="{{ $t('search_placeholder') }}"
                           class="ss-input !h-12 !pl-10">
                </div>
                <x-button type="submit" variant="secondary" class="!h-12">{{ $t('search_button') }}</x-button>
            </form>
        </div>
    </section>

    <div class="ss-container">
        @if ($results !== null)
            <section class="mt-12" aria-labelledby="results-title">
                <div class="flex flex-wrap items-baseline justify-between gap-3">
                    <h2 id="results-title" class="text-lg font-semibold text-white" aria-live="polite">{{ $t('results') }} “{{ $query }}” <span class="font-mono text-sm font-normal text-slate-500">({{ count($results) }})</span></h2>
                    <a href="{{ url($lang . '/docs') }}" class="text-sm text-sky-300 hover:text-sky-200">{{ $t('clear') }}</a>
                </div>
                @if ($results === [])
                    <x-empty-state icon="search" :title="$t('no_results')" heading="p" class="mt-6">
                        {{ $t('search_hint') }}
                    </x-empty-state>
                @else
                    <ul class="mt-5 grid gap-3">
                        @foreach ($results as $result)
                            <li>
                                <a href="{{ url($lang . '/docs/' . $result['slug']) }}" class="ss-card block p-5">
                                    <span class="font-mono text-[11px] uppercase tracking-wider text-slate-500">{{ $labels[$result['category'] ?? ''] ?? '' }}</span>
                                    <span class="mt-1 block font-semibold text-white">{{ $result['title'] }}</span>
                                    <span class="mt-1.5 block text-sm leading-relaxed text-slate-400">{{ $result['snippet'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endif

        @if ($articles->isEmpty())
            <x-empty-state icon="book" :title="$t('empty_title')" class="mt-12">{{ $t('empty') }}</x-empty-state>
        @else
            @foreach ($groups as $category => $items)
                <section class="mt-14" aria-labelledby="cat-{{ $category }}">
                    <h2 id="cat-{{ $category }}" class="ss-eyebrow" data-reveal>{{ $labels[$category] ?? $category }}</h2>
                    <ul class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                        @foreach ($items as $article)
                            @php $slug = data_get($article, 'slug'); @endphp
                            <li data-reveal>
                                <a href="{{ url($lang . '/docs/' . $slug) }}" class="ss-card group flex h-full flex-col p-5">
                                    <span class="flex items-center justify-between gap-3">
                                        <x-icon-tile :name="data_get($article, 'icon', 'book')" size="sm" />
                                        @if (data_get($article, 'minutes'))
                                            <span class="font-mono text-[11px] text-slate-500">{{ $t('minutes', ['count' => data_get($article, 'minutes')]) }}</span>
                                        @endif
                                    </span>
                                    <span class="mt-4 block font-semibold text-white group-hover:text-sky-200">{{ data_get($article, 'title', \Illuminate\Support\Str::headline($slug)) }}</span>
                                    @if (data_get($article, 'description'))
                                        <span class="mt-1.5 block text-sm leading-relaxed text-slate-400">{{ data_get($article, 'description') }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        @endif
    </div>
@endsection
