@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.docs.' . $key, [], $lang);
    $d = fn (string $key) => trans('site.docs2.' . $key, [], $lang);
    $title = data_get($article ?? null, 'title', $t('fallback_title'));
    $body = data_get($article ?? null, 'html', data_get($article ?? null, 'content', ''));
    $others = collect($articles ?? [])->filter(fn ($item) => filled(data_get($item, 'slug')))->values();
    $current = data_get($article ?? null, 'slug');
    $description = data_get($article ?? null, 'description');
    $toc = data_get($article ?? null, 'toc', []);
    $previous = data_get($article ?? null, 'previous');
    $next = data_get($article ?? null, 'next');
    $updated = data_get($article ?? null, 'updated_at');
    $docId = data_get($article ?? null, 'id');
    $app = config('serverscope.app_url');
    $seoTitle = data_get($article ?? null, 'seo_title') ?: $title;
    $seoDescription = data_get($article ?? null, 'seo_description') ?: $description;
@endphp

@section('og_eyebrow', $t('headline'))
@section('title', $seoTitle . ' — ServerScope')
@if (filled($seoDescription))
    @section('description', \Illuminate\Support\Str::limit(strip_tags((string) $seoDescription), 160))
@endif
@if (data_get($article ?? null, 'noindex') || ($preview ?? false))
    @section('noindex', '1')
@endif

@unless ($preview ?? false)
    @php \App\Support\StructuredData::push(\App\Support\StructuredData::techArticle($title, $seoDescription ? strip_tags((string) $seoDescription) : null, $lang, $updated)); @endphp
@endunless

@section('content')
    <div class="ss-progress" data-reading-progress aria-hidden="true"></div>

    <div class="ss-container grid gap-10 pt-10 lg:grid-cols-[14rem_minmax(0,1fr)] xl:grid-cols-[14rem_minmax(0,1fr)_12rem]">
        {{-- Sidebar --}}
        <aside class="lg:sticky lg:top-24 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto lg:pb-10">
            <form method="GET" action="{{ url($lang . '/docs') }}" role="search" class="relative">
                <label for="docs-search" class="sr-only">{{ $t('search_label') }}</label>
                <x-ss-icon name="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-subtle" />
                <input id="docs-search" type="search" name="q" maxlength="100" placeholder="{{ $t('search_button') }}…" class="ss-input !py-2 !pl-9 !text-sm">
            </form>

            @if ($others->isNotEmpty())
                {{-- Small screens: collapsed so the article comes first. --}}
                <details class="group mt-4 rounded-xl border border-line bg-surface lg:hidden">
                    <summary class="flex cursor-pointer list-none items-center justify-between px-3 py-2.5 text-sm font-semibold text-fg [&::-webkit-details-marker]:hidden">
                        {{ $t('guides') }} ({{ $others->count() }})
                        <x-ss-icon name="arrow" class="h-4 w-4 rotate-90 transition-transform group-open:-rotate-90" />
                    </summary>
                    <div class="border-t border-line p-2">@include('docs.partials.guide-list', ['class' => ''])</div>
                </details>
                <nav class="mt-6 hidden lg:block" aria-label="{{ $t('headline') }}">
                    @include('docs.partials.guide-list', ['class' => ''])
                </nav>
            @endif
        </aside>

        {{-- Article --}}
        <article class="min-w-0" data-article>
            <x-breadcrumbs class="mb-5" :items="[[$t('home'), url($lang)], [$t('headline'), url($lang . '/docs')], [$title, null]]" />

            @if ($preview ?? false)
                <p class="mb-5 rounded-xl border border-accent-line bg-accent-soft px-4 py-3 text-sm text-fg" role="note"><strong>{{ trans('site.common.example', [], $lang) }}</strong> — niet opgeslagen en niet gepubliceerd.</p>
            @endif
            @if (data_get($article ?? null, 'coming_soon'))
                <p class="mb-5 flex flex-wrap items-center gap-2 rounded-xl border border-accent-line bg-accent-soft px-4 py-3 text-sm text-fg" role="note">
                    <span class="ss-badge ss-badge-soon">{{ $t('coming_soon') }}</span> {{ $t('coming_soon_text') }}
                </p>
            @endif

            <h1 class="text-4xl font-semibold leading-tight tracking-[-0.03em] text-fg">{{ $title }}</h1>
            @if (filled($description))
                <p class="mt-4 max-w-2xl text-lg leading-relaxed text-muted">{{ $description }}</p>
            @endif
            @if ($updated)
                <p class="mt-4 text-xs text-subtle">{{ trans('site.common.last_updated', [], $lang) }}: <time datetime="{{ $updated->toIso8601String() }}">{{ $updated->locale($lang)->translatedFormat('j F Y') }}</time></p>
            @endif

            <x-prose :html="$body" class="mt-10" data-copy-label="{{ $t('copy') }}" data-copied-label="{{ $t('copied') }}" data-anchor-label="{{ $d('copy_link') }}" />

            {{-- Was this helpful? Works without JavaScript (plain form post). --}}
            @if ($docId && ! ($preview ?? false))
                <section class="mt-14 rounded-2xl border border-line bg-surface p-6" aria-labelledby="feedback-title" data-feedback>
                    @if (session('feedback-sent') === $docId)
                        <p class="text-sm font-medium text-fg" role="status">{{ $d('thanks') }}</p>
                    @else
                        <form method="POST" action="{{ url($lang . '/docs/' . $current . '/feedback') }}" data-feedback-form>
                            @csrf
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <h2 id="feedback-title" class="font-semibold text-fg">{{ $d('helpful') }}</h2>
                                <div class="flex gap-2" role="group" aria-labelledby="feedback-title">
                                    <button type="submit" name="helpful" value="1" class="ss-btn ss-btn-secondary !min-h-9 !py-1.5" data-feedback-choice><x-ss-icon name="thumb-up" class="h-4 w-4" /> {{ $d('yes') }}</button>
                                    <button type="submit" name="helpful" value="0" class="ss-btn ss-btn-secondary !min-h-9 !py-1.5" data-feedback-choice><x-ss-icon name="thumb-down" class="h-4 w-4" /> {{ $d('no') }}</button>
                                </div>
                            </div>
                            <div class="mt-4" data-feedback-comment>
                                <label for="feedback-comment" class="text-sm text-muted">{{ $d('comment') }}</label>
                                <textarea id="feedback-comment" name="comment" rows="2" maxlength="500" class="ss-input mt-1.5"></textarea>
                            </div>
                            <p class="mt-3 text-xs text-subtle">{{ $d('privacy') }}</p>
                        </form>
                        <p class="hidden text-sm font-medium text-fg" role="status" data-feedback-thanks>{{ $d('thanks') }}</p>
                    @endif
                </section>
            @endif

            <div class="mt-10 grid gap-4 sm:grid-cols-2">
                <div class="ss-card p-5">
                    <p class="font-semibold text-fg">{{ $t('app_title') }}</p>
                    <p class="mt-1 text-sm text-muted">{{ $t('app_text') }}</p>
                    <x-button :href="$app . '/login'" class="mt-4 !min-h-9 !py-1.5" data-app-link>{{ trans('site.nav.login', [], $lang) }} <x-ss-icon name="arrow" class="ss-arrow h-4 w-4" /></x-button>
                </div>
                <div class="ss-card p-5">
                    <p class="font-semibold text-fg">{{ $t('help_title') }}</p>
                    <p class="mt-1 text-sm text-muted">{{ $t('help_text') }}</p>
                    <x-button :href="url($lang . '/contact')" variant="secondary" class="mt-4 !min-h-9 !py-1.5">{{ trans('site.nav.contact', [], $lang) }}</x-button>
                </div>
            </div>

            @if ($previous || $next)
                <nav class="mt-10 grid gap-3 border-t border-line pt-8 sm:grid-cols-2" aria-label="{{ $t('previous') }} / {{ $t('next') }}">
                    @if ($previous)
                        <a href="{{ url($lang . '/docs/' . $previous['slug']) }}" rel="prev" class="ss-card group p-5">
                            <span class="flex items-center gap-1.5 text-xs font-semibold uppercase tracking-[.08em] text-subtle"><x-ss-icon name="arrow" class="h-3.5 w-3.5 rotate-180" /> {{ $t('previous') }}</span>
                            <span class="mt-1 block font-semibold text-fg group-hover:text-accent">{{ $previous['title'] }}</span>
                        </a>
                    @else
                        <span class="hidden sm:block"></span>
                    @endif
                    @if ($next)
                        <a href="{{ url($lang . '/docs/' . $next['slug']) }}" rel="next" class="ss-card group p-5 text-right">
                            <span class="flex items-center justify-end gap-1.5 text-xs font-semibold uppercase tracking-[.08em] text-subtle">{{ $t('next') }} <x-ss-icon name="arrow" class="h-3.5 w-3.5" /></span>
                            <span class="mt-1 block font-semibold text-fg group-hover:text-accent">{{ $next['title'] }}</span>
                        </a>
                    @endif
                </nav>
            @endif
        </article>

        {{-- On this page (follows the reader) --}}
        @if (count($toc) > 1)
            <aside class="hidden xl:sticky xl:top-24 xl:block xl:h-fit">
                <p class="text-xs font-semibold uppercase tracking-[.08em] text-subtle">{{ $t('on_this_page') }}</p>
                <ul class="ss-toc mt-3 border-l border-line" data-toc>
                    @foreach ($toc as $entry)
                        <li><a href="#{{ $entry['id'] }}">{{ $entry['title'] }}</a></li>
                    @endforeach
                </ul>
            </aside>
        @endif
    </div>
@endsection
