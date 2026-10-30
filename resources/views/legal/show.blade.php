@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $l = fn (string $key) => trans('site.legal.' . $key, [], $lang);
    $date = fn ($d) => $d?->locale($lang)->translatedFormat('j F Y');
    $versionsUrl = url($document->path() . '/' . ($lang === 'en' ? 'versions' : 'versies'));
@endphp

@section('og_eyebrow', $l('legal'))
@section('title', $document->title . ' — ServerScope')
@section('description', $document->seo_description ?: $document->title)
@if (($preview ?? false) || ($archived ?? false))
    @section('noindex', '1')
@else
    @push('head')
        <link rel="alternate" type="text/markdown" href="{{ url($document->path() . '.md') }}" title="Markdown">
    @endpush
@endif

@section('content')
    <div class="ss-progress" data-reading-progress aria-hidden="true"></div>

    @if ($preview ?? false)
        <div class="border-b border-line bg-accent-soft">
            <p class="ss-container py-3 text-sm text-fg" role="note">
                <strong>{{ trans('site.common.example', [], $lang) }}</strong> — {{ $version->isPublished() ? $l('preview_published') : $l('preview_draft') }}
                @unless ($version->legal_reviewed)
                    <span class="ss-badge ss-badge-soon ml-2">Concept: nog niet juridisch gecontroleerd</span>
                @endunless
            </p>
        </div>
    @endif

    <div class="ss-container grid gap-12 pt-12 xl:grid-cols-[minmax(0,1fr)_14rem]">
        <article class="ss-legal mx-auto w-full min-w-0 max-w-[44rem]" data-article>
            <x-breadcrumbs class="mb-6 ss-noprint" :items="[[trans('site.docs.home', [], $lang), url($lang)], [$l('legal'), null], [$document->title, null]]" />

            <p class="ss-eyebrow ss-rise">{{ $l('legal') }}</p>
            <h1 class="ss-rise mt-2 text-4xl font-semibold tracking-[-0.03em] text-fg sm:text-[2.75rem]">{{ $document->title }}</h1>

            <div class="ss-rise ss-rise-2 mt-6 flex flex-wrap items-center gap-x-6 gap-y-2 border-y border-line py-4 text-sm">
                <dl class="flex flex-wrap gap-x-6 gap-y-2">
                    <div class="flex gap-1.5"><dt class="text-muted">{{ $l('version') }}</dt><dd class="font-medium text-fg">{{ $version->version }}</dd></div>
                    <div class="flex gap-1.5"><dt class="text-muted">{{ $l('effective') }}</dt><dd class="font-medium text-fg">{{ $version->effective_at ? $date($version->effective_at) : '—' }}</dd></div>
                </dl>
                @if ($hasVersions ?? false)
                    <div class="ss-noprint"><a href="{{ $versionsUrl }}" class="text-accent hover:underline">{{ $l('earlier') }}</a></div>
                @endif
                <div class="ml-auto ss-noprint">
                    <button type="button" class="inline-flex items-center gap-1.5 text-muted hover:text-fg" data-print>
                        <svg viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M6 14h12v7H6z"/></svg>
                        {{ $l('print') }}
                    </button>
                </div>
            </div>

            @if ($archived ?? false)
                <p class="mt-6 rounded-xl border border-line bg-surface p-4 text-sm text-fg" role="note">{{ $l('archived') }} <a href="{{ url($document->path()) }}" class="text-accent underline">{{ $l('current') }}</a></p>
            @endif
            @if ($upcoming ?? null)
                <p class="mt-6 rounded-xl border border-accent-line bg-accent-soft p-4 text-sm text-fg" role="note">
                    {{ str_replace(':date', $date($upcoming->effective_at), $l('upcoming')) }}
                    <a href="{{ url($document->path() . '/' . ($lang === 'en' ? 'version' : 'versie') . '/' . $upcoming->version) }}" class="text-accent underline">{{ $l('read_upcoming') }}</a>
                </p>
            @endif

            @if (count($toc) > 1)
                <details class="mt-8 rounded-xl border border-line bg-surface xl:hidden ss-noprint">
                    <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-fg">{{ trans('site.docs.on_this_page', [], $lang) }}</summary>
                    <ol class="border-t border-line px-4 py-3 text-sm">
                        @foreach ($toc as $entry)
                            <li><a href="#{{ $entry['id'] }}" class="block py-1 text-muted hover:text-fg">{{ $entry['title'] }}</a></li>
                        @endforeach
                    </ol>
                </details>
            @endif

            <x-prose :html="$html" class="ss-legal-body mt-10" data-copy-label="{{ trans('site.docs.copy', [], $lang) }}" data-copied-label="{{ trans('site.docs.copied', [], $lang) }}" data-anchor-label="{{ trans('site.docs2.copy_link', [], $lang) }}" data-anchor-copy />

            @if (filled($changelog))
                <section class="mt-14 border-t border-line pt-8" aria-labelledby="changes">
                    <h2 id="changes" class="text-lg font-semibold text-fg">{{ $l('changes') }} {{ $version->version }}</h2>
                    <x-prose :html="$changelog" class="mt-3 text-sm" />
                    @if ($hasVersions ?? false)
                        <p class="mt-4 text-sm ss-noprint"><a href="{{ $versionsUrl }}" class="text-accent hover:underline">{{ $l('all_versions') }}</a></p>
                    @endif
                </section>
            @endif
        </article>

        @if (count($toc) > 1)
            <aside class="hidden xl:sticky xl:top-24 xl:block xl:h-fit ss-noprint">
                <p class="text-xs font-semibold uppercase tracking-[.08em] text-subtle">{{ trans('site.docs.on_this_page', [], $lang) }}</p>
                <ul class="ss-toc mt-3 border-l border-line" data-toc>
                    @foreach ($toc as $entry)
                        <li><a href="#{{ $entry['id'] }}">{{ $entry['title'] }}</a></li>
                    @endforeach
                </ul>
            </aside>
        @endif
    </div>
@endsection
