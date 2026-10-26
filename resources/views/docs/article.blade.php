@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key, array $replace = []) => trans('site.docs.' . $key, $replace, $lang);
    $title = data_get($article ?? null, 'title', $t('fallback_title'));
    $body = data_get($article ?? null, 'html', data_get($article ?? null, 'content', ''));
    $current = data_get($article ?? null, 'slug');
    $description = data_get($article ?? null, 'description');
    $toc = data_get($article ?? null, 'toc', []);
    $previous = data_get($article ?? null, 'previous');
    $next = data_get($article ?? null, 'next');
    $category = data_get($article ?? null, 'category');
    $minutes = data_get($article ?? null, 'minutes');
    $groups = $groups ?? [];
    $categoryLabel = $category ? ($t('categories')[$category] ?? null) : null;
    $app = config('serverscope.app_url');
@endphp

@section('title', $title . ' — ServerScope')
@if (filled($description))
    @section('description', \Illuminate\Support\Str::limit(strip_tags((string) $description), 160))
@endif
@section('og_type', 'article')

@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@graph' => array_values(array_filter([
                ['@type' => 'TechArticle', 'headline' => $title, 'description' => $description, 'inLanguage' => $lang, 'url' => url()->current(), 'publisher' => ['@type' => 'Organization', 'name' => 'ServerScope']],
                ['@type' => 'BreadcrumbList', 'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => $t('home'), 'item' => url($lang)],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $t('headline'), 'item' => url($lang . '/docs')],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $title],
                ]],
            ])),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
    </script>
@endpush

@section('content')
    <div class="ss-progress" data-progress="#doc-article" aria-hidden="true"></div>

    <div class="ss-container grid gap-10 pt-8 lg:grid-cols-[14.5rem_minmax(0,1fr)] lg:pt-12 xl:max-w-[84rem] xl:grid-cols-[14.5rem_minmax(0,1fr)_13rem]">
        {{-- Sidebar --}}
        <aside class="lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:self-start lg:overflow-y-auto lg:pb-10 lg:pr-2" data-print-hidden>
            <a href="{{ url($lang . '/docs') }}" data-search-open
               class="flex h-10 w-full items-center gap-2.5 rounded-lg border border-white/10 bg-white/[.03] px-3 text-sm text-slate-500 transition hover:border-white/20 hover:text-slate-300">
                <x-ss-icon name="search" class="h-4 w-4" />
                <span class="flex-1 text-left">{{ $t('search_button') }}…</span>
                <kbd class="ss-kbd">/</kbd>
            </a>

            @if ($groups !== [])
                {{-- Small screens: collapsed so the article comes first. --}}
                <details class="group mt-4 rounded-lg border border-white/10 bg-white/[.02] lg:hidden">
                    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between px-3 text-sm font-medium text-slate-300 [&::-webkit-details-marker]:hidden">
                        <span class="flex items-center gap-2"><x-ss-icon name="book" class="h-4 w-4 text-slate-500" /> {{ $t('menu') }}</span>
                        <x-ss-icon name="chevron" class="h-4 w-4 rotate-90 transition group-open:-rotate-90" />
                    </summary>
                    <nav class="border-t border-white/10 p-3" aria-label="{{ $t('menu') }}">
                        @include('docs.partials.sidebar')
                    </nav>
                </details>
                <nav class="mt-7 hidden lg:block" aria-label="{{ $t('headline') }}">
                    @include('docs.partials.sidebar')
                </nav>
            @endif
        </aside>

        {{-- Article --}}
        <article id="doc-article" class="min-w-0">
            <nav aria-label="Breadcrumb" class="text-sm text-slate-500">
                <ol class="flex flex-wrap items-center gap-1.5">
                    <li><a href="{{ url($lang) }}" class="hover:text-slate-200">{{ $t('home') }}</a></li>
                    <li aria-hidden="true"><x-ss-icon name="chevron" class="h-3.5 w-3.5" /></li>
                    <li><a href="{{ url($lang . '/docs') }}" class="hover:text-slate-200">{{ $t('headline') }}</a></li>
                    <li aria-hidden="true"><x-ss-icon name="chevron" class="h-3.5 w-3.5" /></li>
                    <li aria-current="page" class="min-w-0 truncate text-slate-300">{{ $title }}</li>
                </ol>
            </nav>

            @if ($categoryLabel)
                <p class="ss-eyebrow mt-8">{{ $categoryLabel }}</p>
            @endif
            <h1 class="mt-3 text-3xl font-semibold leading-tight tracking-[-0.03em] text-white sm:text-[2.5rem]">{{ $title }}</h1>
            @if (filled($description))
                <p class="ss-lead mt-4 max-w-2xl">{{ $description }}</p>
            @endif
            @if ($minutes)
                <p class="mt-5 flex flex-wrap items-center gap-x-4 gap-y-1 font-mono text-xs text-slate-500">
                    @if ($minutes)<span class="inline-flex items-center gap-1.5"><x-ss-icon name="clock" class="h-3.5 w-3.5" /> {{ $t('minutes', ['count' => $minutes]) }}</span>@endif
                </p>
            @endif

            {{-- On this page (small screens) --}}
            @if (count($toc) > 1)
                <details class="group mt-8 rounded-lg border border-white/10 bg-white/[.02] xl:hidden" data-print-hidden>
                    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between px-3 text-sm font-medium text-slate-300 [&::-webkit-details-marker]:hidden">
                        {{ $t('on_this_page') }} <x-ss-icon name="chevron" class="h-4 w-4 rotate-90 transition group-open:-rotate-90" />
                    </summary>
                    <ul class="space-y-1 border-t border-white/10 p-3 text-sm">
                        @foreach ($toc as $entry)
                            <li><a href="#{{ $entry['id'] }}" class="block rounded px-2 py-1.5 text-slate-400 hover:text-white">{{ $entry['title'] }}</a></li>
                        @endforeach
                    </ul>
                </details>
            @endif

            <x-prose :html="$body" class="ss-docs mt-10" data-code-blocks
                     data-copy-label="{{ $t('copy') }}" data-copied-label="{{ $t('copied') }}" data-tabs-label="{{ $t('tabs_label') }}" />

            @if ($previous || $next)
                <nav class="mt-16 grid gap-3 sm:grid-cols-2" aria-label="{{ $t('previous') }} / {{ $t('next') }}" data-print-hidden>
                    @if ($previous)
                        <a href="{{ url($lang . '/docs/' . $previous['slug']) }}" rel="prev" class="ss-card group p-5">
                            <span class="flex items-center gap-1.5 font-mono text-[11px] uppercase tracking-wider text-slate-500"><x-ss-icon name="arrow" class="h-3.5 w-3.5 rotate-180" /> {{ $t('previous') }}</span>
                            <span class="mt-1.5 block font-semibold text-white group-hover:text-sky-200">{{ $previous['title'] }}</span>
                        </a>
                    @else
                        <span class="hidden sm:block"></span>
                    @endif
                    @if ($next)
                        <a href="{{ url($lang . '/docs/' . $next['slug']) }}" rel="next" class="ss-card group p-5 text-right">
                            <span class="flex items-center justify-end gap-1.5 font-mono text-[11px] uppercase tracking-wider text-slate-500">{{ $t('next') }} <x-ss-icon name="arrow" class="h-3.5 w-3.5" /></span>
                            <span class="mt-1.5 block font-semibold text-white group-hover:text-sky-200">{{ $next['title'] }}</span>
                        </a>
                    @endif
                </nav>
            @endif

            <div class="mt-6 grid gap-3 sm:grid-cols-2" data-print-hidden>
                <div class="ss-panel p-5">
                    <p class="font-semibold text-white">{{ $t('app_title') }}</p>
                    <p class="mt-1 text-sm leading-relaxed text-slate-400">{{ $t('app_text') }}</p>
                    <x-button :href="$app . '/login'" size="sm" class="mt-4" :arrow="true" data-app-link>{{ trans('site.nav.login', [], $lang) }}</x-button>
                </div>
                <div class="ss-panel p-5">
                    <p class="font-semibold text-white">{{ $t('help_title') }}</p>
                    <p class="mt-1 text-sm leading-relaxed text-slate-400">{{ $t('help_text') }}</p>
                    <x-button :href="url($lang . '/contact') . '?' . http_build_query(['subject' => $t('feedback_subject', ['title' => $title])])" variant="secondary" size="sm" class="mt-4">{{ $t('help_cta') }}</x-button>
                </div>
            </div>
        </article>

        {{-- On this page (wide screens) --}}
        @if (count($toc) > 1)
            <aside class="hidden xl:sticky xl:top-24 xl:block xl:max-h-[calc(100vh-7rem)] xl:self-start xl:overflow-y-auto" data-print-hidden>
                <nav aria-labelledby="toc-title" data-toc>
                    <p id="toc-title" class="font-mono text-[11px] font-medium uppercase tracking-wider text-slate-500">{{ $t('on_this_page') }}</p>
                    <ul class="mt-3 space-y-px border-l border-white/[.07] text-[13px]">
                        @foreach ($toc as $entry)
                            <li><a href="#{{ $entry['id'] }}" class="-ml-px block border-l border-transparent py-1 pl-3 leading-snug text-slate-500 transition hover:text-slate-200 data-active:border-sky-400 data-active:text-sky-200">{{ $entry['title'] }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            </aside>
        @endif
    </div>
@endsection
