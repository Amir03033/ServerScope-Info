@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.docs.' . $key, [], $lang);
    $articles = collect($articles ?? [])->filter(fn ($article) => filled(data_get($article, 'slug')));
@endphp

@section('title', $t('title'))

@section('content')
    <section class="mx-auto max-w-4xl px-4 pt-20 sm:px-6">
        <x-page-header :title="$t('headline')" :intro="$t('intro')" />

        @if ($articles->isEmpty())
            <x-empty-state icon="book" :title="$t('empty_title')" class="mt-12">{{ $t('empty') }}</x-empty-state>
        @else
            <ul class="mt-12 grid gap-3 sm:grid-cols-2">
                @foreach ($articles as $article)
                    @php $slug = data_get($article, 'slug'); @endphp
                    <li>
                        <a href="{{ url($lang . '/docs/' . $slug) }}" class="group flex h-full items-start gap-4 rounded-2xl border border-white/10 bg-slate-900/50 p-5 transition hover:border-sky-400/30">
                            <x-icon-tile name="book" size="sm" />
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
