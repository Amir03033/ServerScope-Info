@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.changelog.' . $key, [], $lang);

    // Release data comes from the database (admin panel); before the import it
    // falls back to lang/{nl,en}/changelog.php (newest first).
    $entries = $entries ?? trans('changelog.entries', [], $lang);
    $entries = is_array($entries) ? $entries : [];
    $badgeLabels = $t('badges');
@endphp

@section('title', \App\Support\Seo::title('changelog', $lang))
@section('description', \App\Support\Seo::description('changelog', $lang))
@if (\App\Support\Seo::noindex('changelog', $lang))
    @section('noindex', '1')
@endif

@section('content')
    <section class="ss-container max-w-4xl pt-16 sm:pt-24">
        <x-page-header :title="$t('headline')" :intro="$t('intro')" />

        <ol class="mt-14">
            @foreach ($entries as $entry)
                <li data-reveal class="relative grid gap-4 border-t border-line py-10 md:grid-cols-[10rem_1fr]">
                    <div class="md:sticky md:top-24 md:h-fit">
                        <time class="text-sm font-medium text-muted" datetime="{{ $entry['date'] }}">{{ \Illuminate\Support\Carbon::parse($entry['date'])->locale($lang)->translatedFormat('j F Y') }}</time>
                        @if (filled($entry['version'] ?? null) && filled($entry['title'] ?? null))
                            <p class="mt-1 font-mono text-xs text-subtle">v{{ $entry['version'] }}</p>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-xl font-semibold tracking-[-0.015em] text-fg" @if (filled($entry['id'] ?? null)) id="{{ $entry['id'] }}" @endif>{{ filled($entry['title'] ?? null) ? $entry['title'] : 'v' . $entry['version'] }}</h2>
                        @if (filled($entry['html'] ?? null))
                            <x-prose :html="$entry['html']" class="mt-4" />
                        @endif
                        @if (($entry['items'] ?? []) !== [])
                            <ul class="mt-4 space-y-2.5">
                                @foreach ($entry['items'] as [$type, $text])
                                    <li class="flex items-start gap-3">
                                        <span class="ss-badge mt-0.5 shrink-0 {{ $type === 'new' ? 'ss-badge-soon' : '' }}">{{ $badgeLabels[$type] ?? $type }}</span>
                                        <span class="text-fg">{{ $text }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </li>
            @endforeach
        </ol>
    </section>
@endsection
