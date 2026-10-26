@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key, array $replace = []) => trans('site.blog.' . $key, $replace, $lang);
    // Entries without a slug cannot be linked to, so they are not listed.
    $articles = collect($articles ?? [])->filter(fn ($article) => filled(data_get($article, 'slug')))->values();
    $date = fn (?string $value) => $value ? \Illuminate\Support\Carbon::parse($value)->locale($lang)->translatedFormat('j F Y') : null;
    $featured = $articles->first();
    $rest = $articles->slice(1);
@endphp

@section('title', $t('title'))
@section('description', $t('description'))

@section('content')
    <section class="ss-container pt-16 sm:pt-20">
        <x-page-header :eyebrow="$t('eyebrow')" :title="$t('headline')" :intro="$t('intro')" />

        @if ($articles->isEmpty())
            <x-empty-state icon="book" :title="$t('empty_title')" class="mt-12">
                {{ $t('empty') }}
                <x-slot:actions>
                    <x-button :href="url($lang . '/docs')" variant="secondary" :arrow="true">{{ $t('docs') }}</x-button>
                </x-slot:actions>
            </x-empty-state>
        @else
            {{-- Newest article, larger --}}
            <article class="ss-card ss-card-hover group relative mt-12 grid gap-6 p-6 sm:p-10 lg:grid-cols-[1fr_auto] lg:items-end" data-reveal>
                <div class="max-w-2xl">
                    <p class="flex flex-wrap items-center gap-x-3 gap-y-1 font-mono text-[11px] uppercase tracking-wider text-slate-500">
                        <span class="ss-badge ss-tone-info !normal-case !tracking-normal">{{ $t('latest') }}</span>
                        @if ($featured['date'])<time datetime="{{ $featured['date'] }}">{{ $date($featured['date']) }}</time>@endif
                        @if ($featured['minutes'] ?? null)<span>· {{ $t('minutes', ['count' => $featured['minutes']]) }}</span>@endif
                    </p>
                    <h2 class="mt-4 text-2xl font-semibold tracking-tight text-white sm:text-3xl">
                        <a href="{{ url($lang . '/blog/' . $featured['slug']) }}" class="after:absolute after:inset-0 after:rounded-2xl">{{ $featured['title'] }}</a>
                    </h2>
                    @if ($featured['excerpt'])<p class="mt-3 text-[15px] leading-relaxed text-slate-400">{{ $featured['excerpt'] }}</p>@endif
                </div>
                <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-sky-300" aria-hidden="true">{{ $t('read') }} <x-ss-icon name="arrow" class="h-4 w-4 transition group-hover:translate-x-1" /></span>
            </article>

            @if ($rest->isNotEmpty())
                <div class="mt-4 grid gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                    @foreach ($rest as $article)
                        <article data-reveal class="ss-card ss-card-hover group relative flex flex-col p-6">
                            <p class="font-mono text-[11px] uppercase tracking-wider text-slate-500">
                                @if ($article['date'])<time datetime="{{ $article['date'] }}">{{ $date($article['date']) }}</time>@endif
                                @if ($article['minutes'] ?? null) · {{ $t('minutes', ['count' => $article['minutes']]) }}@endif
                            </p>
                            <h2 class="mt-3 text-lg font-semibold leading-snug text-white">
                                <a href="{{ url($lang . '/blog/' . $article['slug']) }}" class="after:absolute after:inset-0 after:rounded-2xl">{{ $article['title'] }}</a>
                            </h2>
                            <p class="mt-2 line-clamp-3 flex-1 text-[15px] leading-relaxed text-slate-400">{{ $article['excerpt'] }}</p>
                            <span class="mt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-sky-300" aria-hidden="true">{{ $t('read') }} <x-ss-icon name="arrow" class="h-4 w-4 transition group-hover:translate-x-1" /></span>
                        </article>
                    @endforeach
                </div>
            @endif
        @endif
    </section>
@endsection
