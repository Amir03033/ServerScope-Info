@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $isEn = $lang === 'en';
    $articles = collect($articles ?? []);
    $t = $isEn ? [
        'title' => 'Documentation — ServerScope',
        'headline' => 'Documentation',
        'intro' => 'Guides and technical documentation for ServerScope.',
        'empty_title' => 'No documentation in this language yet',
        'empty' => 'Documentation is still being written. Come back soon.',
        'open' => 'Read',
    ] : [
        'title' => 'Documentatie — ServerScope',
        'headline' => 'Documentatie',
        'intro' => 'Handleidingen en technische documentatie voor ServerScope.',
        'empty_title' => 'Nog geen documentatie in deze taal',
        'empty' => 'De documentatie wordt nog geschreven. Kom snel terug.',
        'open' => 'Lees',
    ];
@endphp

@section('title', $t['title'])

@section('content')
    <section class="mx-auto max-w-4xl px-4 pt-20 sm:px-6">
        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl">{{ $t['headline'] }}</h1>
        <p class="mt-4 max-w-xl text-lg text-slate-400">{{ $t['intro'] }}</p>

        @if ($articles->isEmpty())
            <div class="mt-12 rounded-3xl border border-dashed border-white/10 bg-slate-900/40 p-10 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl border border-sky-400/20 bg-sky-400/10 text-sky-400"><x-icon name="book" /></span>
                <h2 class="mt-5 text-xl font-semibold text-white">{{ $t['empty_title'] }}</h2>
                <p class="mx-auto mt-2 max-w-md text-slate-400">{{ $t['empty'] }}</p>
            </div>
        @else
            <ul class="mt-12 grid gap-3 sm:grid-cols-2">
                @foreach ($articles as $article)
                    @php $slug = data_get($article, 'slug'); @endphp
                    <li>
                        <a href="{{ url($lang . '/docs/' . $slug) }}" class="group flex h-full items-start gap-4 rounded-2xl border border-white/10 bg-slate-900/50 p-5 transition hover:border-sky-400/30">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-sky-400/20 bg-sky-400/10 text-sky-400"><x-icon name="book" /></span>
                            <span>
                                <span class="block font-semibold text-white group-hover:text-sky-300">{{ data_get($article, 'title', \Illuminate\Support\Str::headline($slug)) }}</span>
                                @if (data_get($article, 'description'))
                                    <span class="mt-1 block text-sm leading-relaxed text-slate-400">{{ data_get($article, 'description') }}</span>
                                @endif
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
