@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $isEn = $lang === 'en';
    $articles = collect($articles ?? []);
    $t = $isEn ? [
        'title' => 'Blog — ServerScope',
        'headline' => 'Blog',
        'intro' => 'News and articles about ServerScope.',
        'empty_title' => 'No articles yet',
        'empty' => 'The first articles are on their way. Meanwhile, the documentation explains how ServerScope works.',
        'docs' => 'Read the documentation',
        'read' => 'Read article',
    ] : [
        'title' => 'Blog — ServerScope',
        'headline' => 'Blog',
        'intro' => 'Nieuws en artikelen over ServerScope.',
        'empty_title' => 'Nog geen artikelen',
        'empty' => 'De eerste artikelen komen eraan. Intussen legt de documentatie uit hoe ServerScope werkt.',
        'docs' => 'Lees de documentatie',
        'read' => 'Lees artikel',
    ];
@endphp

@section('title', $t['title'])

@section('content')
    <section class="mx-auto max-w-6xl px-4 pt-20 sm:px-6">
        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $t['headline'] }}</h1>
        <p class="mt-4 max-w-xl text-lg text-slate-400">{{ $t['intro'] }}</p>

        @if ($articles->isEmpty())
            <div class="mt-12 rounded-3xl border border-dashed border-white/10 bg-slate-900/40 p-10 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl border border-sky-400/20 bg-sky-400/10 text-sky-400"><x-icon name="book" /></span>
                <h2 class="mt-5 text-xl font-semibold text-white">{{ $t['empty_title'] }}</h2>
                <p class="mx-auto mt-2 max-w-md text-slate-400">{{ $t['empty'] }}</p>
                <x-button :href="url($lang . '/docs')" variant="secondary" class="mt-6">{{ $t['docs'] }}</x-button>
            </div>
        @else
            <div class="mt-12 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    @php
                        $slug = data_get($article, 'slug');
                        $date = data_get($article, 'date');
                    @endphp
                    <article class="flex flex-col rounded-2xl border border-white/10 bg-slate-900/50 p-6 transition hover:border-sky-400/30">
                        @if ($date)<time class="text-xs text-slate-500">{{ $date }}</time>@endif
                        <h2 class="mt-2 text-lg font-semibold text-white">
                            <a href="{{ url($lang . '/blog/' . $slug) }}" class="hover:text-sky-300">{{ data_get($article, 'title') }}</a>
                        </h2>
                        <p class="mt-2 flex-1 leading-relaxed text-slate-400">{{ data_get($article, 'excerpt') }}</p>
                        <a href="{{ url($lang . '/blog/' . $slug) }}" class="mt-5 inline-flex items-center gap-2 text-sm font-semibold text-sky-300 hover:text-sky-200">
                            {{ $t['read'] }} <x-icon name="arrow" class="h-4 w-4" />
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
