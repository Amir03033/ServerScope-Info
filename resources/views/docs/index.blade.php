@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.docs.' . $key, [], $lang);
    $d = fn (string $key) => trans('site.docs2.' . $key, [], $lang);
    $articles = collect($articles ?? [])->filter(fn ($article) => filled(data_get($article, 'slug')))->values();
    $groups = $articles->groupBy(fn ($a) => data_get($a, 'category.name') ?? '');
    $query = (string) ($query ?? '');
    $results = $results ?? null;
@endphp

@section('title', $t('title'))
@section('description', $t('intro'))

@section('content')
    <section class="relative border-b border-line">
        <div class="ss-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="ss-container relative pb-14 pt-12 sm:pt-16">
            <nav aria-label="Breadcrumb" class="mb-8 text-sm text-muted">
                <ol class="flex flex-wrap items-center gap-2">
                    <li><a href="{{ url($lang) }}" class="hover:text-fg">{{ $t('home') }}</a></li>
                    <li aria-hidden="true" class="text-subtle">/</li>
                    <li aria-current="page" class="text-fg">{{ $t('headline') }}</li>
                </ol>
            </nav>

            <x-page-header :title="$t('headline')" :intro="$t('intro')" />

            <form method="GET" action="{{ url($lang . '/docs') }}" role="search" class="ss-rise ss-rise-3 mt-8 flex max-w-xl gap-2">
                <label for="docs-search" class="sr-only">{{ $t('search_label') }}</label>
                <div class="relative flex-1">
                    <x-ss-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-subtle" />
                    <input id="docs-search" type="search" name="q" value="{{ $query }}" maxlength="100" placeholder="{{ $t('search_placeholder') }}" class="ss-input !pl-10">
                </div>
                <x-button type="submit" variant="secondary">{{ $t('search_button') }}</x-button>
            </form>
            <p class="mt-3 hidden text-xs text-subtle sm:block" data-shortcut-hint>{{ $d('shortcut') }}: <kbd class="ss-kbd" data-shortcut-label>Ctrl K</kbd></p>
        </div>
    </section>

    <div class="ss-container">
        @if ($results !== null)
            <div class="mt-12" aria-live="polite">
                <div class="flex flex-wrap items-baseline justify-between gap-3">
                    <h2 class="text-lg font-semibold text-fg">{{ $t('results') }} “{{ $query }}”</h2>
                    <a href="{{ url($lang . '/docs') }}" class="text-sm text-accent hover:underline">{{ $t('clear') }}</a>
                </div>
                @if ($results === [])
                    <p class="mt-4 text-muted">{{ $t('no_results') }}</p>
                @else
                    <ul class="mt-5 grid gap-3">
                        @foreach ($results as $result)
                            <li>
                                <a href="{{ url($lang . '/docs/' . $result['slug']) }}" class="ss-card block p-5">
                                    <span class="block font-semibold text-fg">{{ $result['title'] }}</span>
                                    <span class="mt-1 block text-sm leading-relaxed text-muted">{{ $result['snippet'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @if ($articles->isEmpty())
            <x-empty-state icon="book" :title="$t('empty_title')" class="mt-12">{{ $t('empty') }}</x-empty-state>
        @else
            @foreach ($groups as $category => $items)
                <section class="mt-14" @if ($category !== '') aria-labelledby="cat-{{ $loop->index }}" @endif>
                    <div class="flex items-baseline justify-between gap-4 border-b border-line pb-3">
                        <h2 id="cat-{{ $loop->index }}" class="text-sm font-semibold uppercase tracking-[.08em] text-subtle">{{ $category !== '' ? $category : $t('guides') }}</h2>
                        <span class="text-xs text-subtle">{{ $items->count() }} {{ $d('guides_count') }}</span>
                    </div>
                    <ol class="mt-5 grid gap-3 sm:grid-cols-2" data-reveal-group>
                        @foreach ($items as $article)
                            @php $slug = data_get($article, 'slug'); @endphp
                            <li data-reveal>
                                <a href="{{ url($lang . '/docs/' . $slug) }}" class="ss-card group flex h-full items-start gap-4 p-5">
                                    <x-icon-tile :name="data_get($article, 'icon', 'book')" size="sm" />
                                    <span class="min-w-0">
                                        <span class="flex flex-wrap items-center gap-2 font-semibold text-fg group-hover:text-accent">
                                            {{ data_get($article, 'title', \Illuminate\Support\Str::headline($slug)) }}
                                            @if (data_get($article, 'coming_soon'))
                                                <span class="ss-badge ss-badge-soon">{{ trans('site.common.coming_soon', [], $lang) }}</span>
                                            @endif
                                        </span>
                                        @if (data_get($article, 'description'))
                                            <span class="mt-1 block text-sm leading-relaxed text-muted">{{ data_get($article, 'description') }}</span>
                                        @endif
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endforeach
        @endif
    </div>
@endsection
