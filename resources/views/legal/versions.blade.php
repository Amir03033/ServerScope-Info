@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $l = fn (string $key) => trans('site.legal.' . $key, [], $lang);
@endphp

@section('title', $document->title . ' — ' . $l('versions') . ' — ServerScope')
@section('description', $l('versions') . ': ' . $document->title)

@section('content')
    <section class="ss-container max-w-3xl pt-12 sm:pt-16">
        <nav aria-label="Breadcrumb" class="mb-6 text-sm text-muted">
            <ol class="flex flex-wrap items-center gap-2">
                <li><a href="{{ url($lang) }}" class="hover:text-fg">{{ trans('site.docs.home', [], $lang) }}</a></li>
                <li aria-hidden="true" class="text-subtle">/</li>
                <li><a href="{{ url($document->path()) }}" class="hover:text-fg">{{ $document->title }}</a></li>
                <li aria-hidden="true" class="text-subtle">/</li>
                <li aria-current="page" class="text-fg">{{ $l('versions') }}</li>
            </ol>
        </nav>

        <x-page-header :title="$document->title" :intro="$l('versions_intro')" />

        <ol class="mt-10 divide-y divide-line rounded-2xl border border-line bg-surface">
            @foreach ($versions as $item)
                <li class="p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ url($document->path() . '/' . ($lang === 'en' ? 'version' : 'versie') . '/' . $item->version) }}" class="font-semibold text-fg hover:text-accent">
                            {{ $l('version') }} {{ $item->version }}
                        </a>
                        <span class="flex items-center gap-2 text-sm text-muted">
                            {{ $l('effective') }} {{ $item->effective_at?->locale($lang)->translatedFormat('j F Y') }}
                            @if ($item->is($current))<span class="ss-badge ss-badge-soon">{{ $l('in_force') }}</span>@endif
                        </span>
                    </div>
                    @if (filled($item->changelog))
                        <p class="mt-2 text-sm text-muted">{{ \Illuminate\Support\Str::limit(strip_tags($item->changelog), 280) }}</p>
                    @endif
                </li>
            @endforeach
        </ol>
    </section>
@endsection
