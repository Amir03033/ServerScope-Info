@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key, array $replace = []) => trans('site.blog.' . $key, $replace, $lang);
    $title = data_get($article ?? null, 'title', $t('fallback_title'));
    $date = data_get($article ?? null, 'date');
    $excerpt = data_get($article ?? null, 'excerpt');
    $minutes = data_get($article ?? null, 'minutes');
    $body = data_get($article ?? null, 'html', data_get($article ?? null, 'content', ''));
    $related = $related ?? [];
    $format = fn (?string $value) => $value ? \Illuminate\Support\Carbon::parse($value)->locale($lang)->translatedFormat('j F Y') : null;
@endphp

@section('title', $title . ' — ServerScope')
@if (filled($excerpt))
    @section('description', \Illuminate\Support\Str::limit(strip_tags((string) $excerpt), 160))
@endif
@section('og_type', 'article')

@push('meta')
    @if (data_get($article ?? null, 'published'))
        <meta property="article:published_time" content="{{ data_get($article, 'published') }}">
    @endif
    @if (data_get($article ?? null, 'updated'))
        <meta property="article:modified_time" content="{{ data_get($article, 'updated') }}">
    @endif
@endpush

@push('head')
    @if ($date)
        <script type="application/ld+json">
            {!! json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'BlogPosting',
                'headline' => $title,
                'description' => $excerpt,
                'inLanguage' => $lang,
                'url' => url()->current(),
                'datePublished' => data_get($article, 'published', $date),
                'dateModified' => data_get($article, 'updated', $date),
                'publisher' => ['@type' => 'Organization', 'name' => 'ServerScope'],
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
        </script>
    @endif
@endpush

@section('content')
    <div class="ss-progress" data-progress="#blog-article" aria-hidden="true"></div>

    <article id="blog-article" class="ss-container max-w-3xl pt-12 sm:pt-16">
        <a href="{{ url($lang . '/blog') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white" data-print-hidden>
            <x-ss-icon name="arrow" class="h-4 w-4 rotate-180" /> {{ $t('all') }}
        </a>

        <header class="mt-8 border-b border-white/[.07] pb-8">
            <p class="flex flex-wrap gap-x-3 font-mono text-xs text-slate-500">
                @if ($date)<time datetime="{{ $date }}">{{ $format($date) }}</time>@endif
                @if ($minutes)<span>· {{ $t('minutes', ['count' => $minutes]) }}</span>@endif
            </p>
            <h1 class="mt-4 text-3xl font-semibold leading-tight tracking-[-0.03em] text-white sm:text-5xl">{{ $title }}</h1>
            @if (filled($excerpt))<p class="ss-lead mt-5">{{ $excerpt }}</p>@endif
        </header>

        <x-prose :html="$body" class="mt-10" data-code-blocks
                 data-copy-label="{{ trans('site.docs.copy', [], $lang) }}" data-copied-label="{{ trans('site.docs.copied', [], $lang) }}" data-tabs-label="{{ trans('site.docs.tabs_label', [], $lang) }}" />
    </article>

    @if ($related !== [])
        <aside class="ss-container mt-20 max-w-5xl" aria-labelledby="related-title" data-print-hidden>
            <h2 id="related-title" class="ss-eyebrow">{{ $t('related') }}</h2>
            <ul class="mt-5 grid gap-4 md:grid-cols-3">
                @foreach ($related as $item)
                    <li>
                        <a href="{{ url($lang . '/blog/' . $item['slug']) }}" class="ss-card block h-full p-5">
                            <time class="font-mono text-[11px] uppercase tracking-wider text-slate-500" datetime="{{ $item['date'] }}">{{ $format($item['date']) }}</time>
                            <span class="mt-2 block font-semibold leading-snug text-white">{{ $item['title'] }}</span>
                            @if ($item['excerpt'])<span class="mt-1.5 line-clamp-2 block text-sm text-slate-400">{{ $item['excerpt'] }}</span>@endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </aside>
    @endif
@endsection
