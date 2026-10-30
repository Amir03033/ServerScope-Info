@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.features.' . $key, [], $lang);
    $c = fn (string $key) => trans('site.common.' . $key, [], $lang);
    $groups = app(\App\Services\Site\SiteData::class)->featuresByCategory();
    $categories = \App\Services\Site\FeatureCatalog::CATEGORIES;
@endphp

@section('title', \App\Support\Seo::title('features', $lang))
@section('description', \App\Support\Seo::description('features', $lang))
@if (\App\Support\Seo::noindex('features', $lang))
    @section('noindex', '1')
@endif

@section('content')
    <section class="relative border-b border-line">
        <div class="ss-grid-bg pointer-events-none absolute inset-0" aria-hidden="true"></div>
        <div class="ss-container relative pb-16 pt-16 sm:pt-24">
            <x-page-header :title="$t('headline')" :intro="$t('v2_intro')" />
        </div>
    </section>

    @if ($groups->isEmpty())
        {{-- Before the database is set up: the original static list. --}}
        <section class="ss-container mt-14">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                @foreach ($t('items') as [$icon, $title, $text])
                    <article data-reveal class="ss-card p-6">
                        <x-icon-tile :name="$icon" />
                        <h2 class="mt-5 font-semibold text-fg">{{ $title }}</h2>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ $text }}</p>
                    </article>
                @endforeach
            </div>
        </section>
    @else
        @foreach ($groups as $category => $features)
            <section class="ss-container mt-16" aria-labelledby="cat-{{ $category }}">
                <h2 id="cat-{{ $category }}" data-reveal class="text-sm font-semibold uppercase tracking-[.08em] text-subtle">
                    {{ $categories[$category][$lang === 'en' ? 1 : 0] ?? \Illuminate\Support\Str::headline($category) }}
                </h2>
                <div class="mt-5 grid gap-px overflow-hidden rounded-2xl border border-line bg-line sm:grid-cols-2 lg:grid-cols-3" data-reveal-group>
                    @foreach ($features as $feature)
                        <article data-reveal class="bg-surface p-6">
                            <div class="flex items-center justify-between gap-3">
                                <x-icon-tile :name="$feature->icon" size="sm" />
                                @if ($feature->isComingSoon())
                                    <span class="ss-badge ss-badge-soon">{{ $c('coming_soon') }}</span>
                                @endif
                            </div>
                            <h3 class="mt-5 font-semibold text-fg">{{ $feature->translate('name', $lang) }}</h3>
                            <p class="mt-1.5 text-sm leading-relaxed text-muted">{{ $feature->translate('summary', $lang) }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach
    @endif

    <section class="ss-container mt-24">
        <h2 data-reveal class="text-2xl font-semibold tracking-[-0.02em] text-fg sm:text-3xl">{{ $t('steps_title') }}</h2>
        <ol class="mt-8 grid gap-4 md:grid-cols-3" data-reveal-group>
            @foreach ($t('steps') as $i => [$title, $text])
                <li data-reveal class="ss-card p-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full border border-accent text-sm font-semibold text-accent">{{ $i + 1 }}</span>
                    <h3 class="mt-4 font-semibold text-fg">{{ $title }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ $text }}</p>
                </li>
            @endforeach
        </ol>
        <div class="mt-10">
            <x-button :href="url($lang . '/docs')">{{ $t('cta') }} <x-ss-icon name="arrow" class="ss-arrow h-4 w-4" /></x-button>
        </div>
    </section>
@endsection
