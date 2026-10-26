@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.docs.' . $key, [], $lang);
    $articles = collect($articles ?? [])->filter(fn ($article) => filled(data_get($article, 'slug')))->values();
    $query = (string) ($query ?? '');
    $results = $results ?? null;
@endphp

@section('title', $t('title'))
@section('description', $t('intro'))

@section('content')
    <section class="mx-auto max-w-5xl px-4 pt-20 sm:px-6">
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-slate-500">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ url($lang) }}" class="hover:text-sky-300">{{ $t('home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-slate-300">{{ $t('headline') }}</li>
            </ol>
        </nav>

        <x-page-header :title="$t('headline')" :intro="$t('intro')" />

        <form method="GET" action="{{ url($lang . '/docs') }}" role="search" class="mt-8 flex max-w-xl gap-2">
            <label for="docs-search" class="sr-only">{{ $t('search_label') }}</label>
            <div class="relative flex-1">
                <x-ss-icon name="search" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" />
                <input id="docs-search" type="search" name="q" value="{{ $query }}" maxlength="100" placeholder="{{ $t('search_placeholder') }}"
                       class="w-full rounded-xl border border-white/10 bg-slate-900/60 py-3 pl-10 pr-3 text-sm text-white placeholder:text-slate-500 focus:border-sky-400/50 focus:outline-none focus:ring-2 focus:ring-sky-400/30">
            </div>
            <x-button type="submit" variant="secondary">{{ $t('search_button') }}</x-button>
        </form>

        @if ($results !== null)
            <div class="mt-10" aria-live="polite">
                <div class="flex flex-wrap items-baseline justify-between gap-3">
                    <h2 class="text-lg font-semibold text-white">{{ $t('results') }} “{{ $query }}”</h2>
                    <a href="{{ url($lang . '/docs') }}" class="text-sm text-sky-300 hover:underline">{{ $t('clear') }}</a>
                </div>
                @if ($results === [])
                    <p class="mt-4 text-slate-400">{{ $t('no_results') }}</p>
                @else
                    <ul class="mt-5 space-y-3">
                        @foreach ($results as $result)
                            <li>
                                <a href="{{ url($lang . '/docs/' . $result['slug']) }}" class="ss-card block rounded-2xl p-5">
                                    <span class="block font-semibold text-white">{{ $result['title'] }}</span>
                                    <span class="mt-1 block text-sm leading-relaxed text-slate-400">{{ $result['snippet'] }}</span>
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
            <h2 class="mt-14 text-sm font-semibold uppercase tracking-wider text-slate-500">{{ $t('guides') }}</h2>
            <ol class="mt-5 grid gap-3 sm:grid-cols-2" data-reveal-group>
                @foreach ($articles as $article)
                    @php $slug = data_get($article, 'slug'); @endphp
                    <li data-reveal>
                        <a href="{{ url($lang . '/docs/' . $slug) }}" class="ss-card group flex h-full items-start gap-4 rounded-2xl p-5">
                            <x-icon-tile :name="data_get($article, 'icon', 'book')" size="sm" />
                            <span>
                                <span class="block text-xs font-semibold text-sky-400/80">{{ $t('step') }} {{ $loop->iteration }}</span>
                                <span class="mt-0.5 block font-semibold text-white group-hover:text-sky-300">{{ data_get($article, 'title', \Illuminate\Support\Str::headline($slug)) }}</span>
                                @if (data_get($article, 'description'))
                                    <span class="mt-1 block text-sm leading-relaxed text-slate-400">{{ data_get($article, 'description') }}</span>
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ol>
        @endif
    </section>
@endsection
