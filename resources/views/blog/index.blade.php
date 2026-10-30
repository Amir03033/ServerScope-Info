@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.blog.' . $key, [], $lang);
    // Entries without a slug cannot be linked to, so they are not listed.
    $articles = collect($articles ?? [])->filter(fn ($article) => filled(data_get($article, 'slug')));
@endphp

@section('title', $t('title'))

@section('content')
    <section class="ss-container pt-16 sm:pt-24">
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
                    <article class="ss-card ss-card-hover flex flex-col p-6">
                        @if ($date)<time class="text-xs text-subtle" datetime="{{ $date }}">{{ \Illuminate\Support\Carbon::parse($date)->locale($lang)->translatedFormat('j F Y') }}</time>@endif
                        <h2 class="mt-2 text-lg font-semibold text-fg">
                            <a href="{{ url($lang . '/blog/' . $slug) }}" class="hover:text-accent">{{ data_get($article, 'title') }}</a>
                        </h2>
                        <p class="mt-2 flex-1 leading-relaxed text-muted">{{ data_get($article, 'excerpt') }}</p>
                        <a href="{{ url($lang . '/blog/' . $slug) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-accent hover:text-accent-hover">
                            {{ $t('read') }} <x-ss-icon name="arrow" class="h-4 w-4" />
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
