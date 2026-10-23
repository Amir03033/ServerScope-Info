@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.blog.' . $key, [], $lang);
    // Entries without a slug cannot be linked to, so they are not listed.
    $articles = collect($articles ?? [])->filter(fn ($article) => filled(data_get($article, 'slug')));
@endphp

@section('title', $t('title'))

@section('content')
    <section class="mx-auto max-w-6xl px-4 pt-20 sm:px-6">
        <x-page-header :title="$t('headline')" :intro="$t('intro')" />

        @if ($articles->isEmpty())
            <x-empty-state icon="book" :title="$t('empty_title')" class="mt-12">
                {{ $t('empty') }}
                <x-slot:actions>
                    <x-button :href="url($lang . '/docs')" variant="secondary" class="mt-6">{{ $t('docs') }}</x-button>
                </x-slot:actions>
            </x-empty-state>
        @else
            <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    @php
                        $slug = data_get($article, 'slug');
                        $date = data_get($article, 'date');
                    @endphp
                    <article class="flex flex-col rounded-2xl border border-white/10 bg-slate-900/50 p-6 transition hover:border-sky-400/30">
                        @if ($date)<time class="text-xs text-slate-500" datetime="{{ $date }}">{{ $date }}</time>@endif
                        <h2 class="mt-2 text-lg font-semibold text-white">
                            <a href="{{ url($lang . '/blog/' . $slug) }}" class="hover:text-sky-300">{{ data_get($article, 'title') }}</a>
                        </h2>
                        <p class="mt-2 flex-1 leading-relaxed text-slate-400">{{ data_get($article, 'excerpt') }}</p>
                        <a href="{{ url($lang . '/blog/' . $slug) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-sky-300 hover:text-sky-200">
                            {{ $t('read') }} <x-icon name="arrow" class="h-4 w-4" />
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
