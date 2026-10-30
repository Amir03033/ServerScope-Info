@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $p = fn (string $key) => trans('site.pages.' . $key, [], $lang);
    $kind = \App\Models\Page::PAGES[$page->key][3] ?? 'about';
    $description = $page->seo_description ?: \Illuminate\Support\Str::limit((string) $page->summary, 160);
@endphp

@section('og_eyebrow', $p($kind))
@section('title', ($page->seo_title ?: $page->title) . ' — ServerScope')
@if (filled($description))
    @section('description', $description)
@endif
@if ($preview)
    @section('noindex', '1')
@else
    @push('head')
        <link rel="alternate" type="text/markdown" href="{{ url($page->path() . '.md') }}" title="Markdown">
    @endpush
    @php
        if ($page->key === 'what-is') {
            \App\Support\StructuredData::push(\App\Support\StructuredData::software($lang));
        }
        if (count($faq)) {
            \App\Support\StructuredData::push(\App\Support\StructuredData::faq(array_map(fn ($item) => ['question' => $item['question'], 'answer' => strip_tags($item['html'])], $faq)));
        }
    @endphp
@endif

@section('content')
    @if ($preview)
        <div class="border-b border-line bg-accent-soft">
            <p class="ss-container py-3 text-sm text-fg" role="note"><strong>{{ $p('preview') }}</strong> — {{ $page->status->value === 'published' ? $p('published') : $p('draft') }}</p>
        </div>
    @endif

    <article class="ss-container max-w-3xl pb-20 pt-12 sm:pt-16">
        <x-breadcrumbs class="mb-6" :items="[[trans('site.docs.home', [], $lang), url($lang)], [$page->title, null]]" />

        <p class="ss-eyebrow ss-rise">{{ $p($kind) }}</p>
        <h1 class="ss-rise mt-2 text-4xl font-semibold tracking-[-0.03em] text-fg sm:text-[2.75rem]">{{ $page->title }}</h1>

        @if ($page->isComingSoon())
            <p class="mt-6 rounded-xl border border-accent-line bg-accent-soft px-4 py-3 text-sm text-fg" role="note">{{ $p('coming_soon') }}</p>
        @endif

        <x-prose :html="$html" class="mt-8" data-copy-label="{{ trans('site.docs.copy', [], $lang) }}" data-copied-label="{{ trans('site.docs.copied', [], $lang) }}" data-anchor-label="{{ trans('site.docs2.copy_link', [], $lang) }}" data-anchor-copy />

        @if (count($faq))
            <section class="mt-14 max-w-[72ch] border-t border-line pt-10" aria-labelledby="faq">
                <h2 id="faq" class="text-2xl font-semibold tracking-tight text-fg">{{ $p('faq') }}</h2>
                <dl class="mt-6 divide-y divide-line">
                    @foreach ($faq as $item)
                        <div class="py-5">
                            <dt class="font-semibold text-fg">{{ $item['question'] }}</dt>
                            <dd><x-prose :html="$item['html']" class="mt-2" /></dd>
                        </div>
                    @endforeach
                </dl>
            </section>
        @endif

        <aside class="ss-card mt-14 max-w-[72ch] p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-fg">{{ $p('cta_title') }}</h2>
            <p class="mt-2 text-muted">{{ $p('cta_text') }}</p>
            <div class="mt-5 flex flex-wrap gap-3">
                <x-button :href="url($lang . '/docs')">{{ $p('docs') }}</x-button>
                <x-button :href="url($lang . '/waitlist')" variant="secondary">{{ $p('waitlist') }}</x-button>
            </div>
        </aside>
    </article>
@endsection
