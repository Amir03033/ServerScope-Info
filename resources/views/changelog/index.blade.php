@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.changelog.' . $key, [], $lang);

    // Provided by ChangelogController (App\Services\Changelog); rendered on
    // its own the view falls back to lang/{locale}/changelog.php.
    $entries = $entries ?? trans('changelog.entries', [], $lang);
    $entries = array_map(
        fn (array $entry) => $entry + ['anchor' => 'v' . str_replace('.', '-', (string) $entry['version'])],
        is_array($entries) ? $entries : [],
    );

    $badge = [
        'new' => 'ss-tone-ok',
        'improved' => 'ss-tone-info',
        'changed' => 'ss-tone-violet',
        'fixed' => 'ss-tone-warn',
    ];
    $badgeLabels = $t('badges');
    $date = fn (string $value) => \Illuminate\Support\Carbon::parse($value)->locale($lang)->translatedFormat('j F Y');
@endphp

@section('title', $t('title'))
@section('description', $t('description'))

@section('content')
    <section class="ss-container pt-16 sm:pt-20">
        <x-page-header :eyebrow="$t('eyebrow')" :title="$t('headline')" :intro="$t('intro')" />

        @if ($entries === [])
            <x-empty-state icon="clock" :title="$t('empty')" heading="p" class="mt-12" />
        @else
            <div class="mt-14 grid gap-10 lg:grid-cols-[12rem_minmax(0,1fr)]">
                @if (count($entries) > 1)
                    <nav class="hidden lg:sticky lg:top-24 lg:block lg:self-start" aria-labelledby="releases-title">
                        <p id="releases-title" class="font-mono text-[11px] font-medium uppercase tracking-wider text-slate-500">{{ $t('releases') }}</p>
                        <ul class="mt-3 space-y-px border-l border-white/[.07] text-sm" data-toc>
                            @foreach ($entries as $entry)
                                <li><a href="#{{ $entry['anchor'] }}" class="-ml-px block border-l border-transparent py-1 pl-3 font-mono text-slate-500 hover:text-slate-200 data-active:border-sky-400 data-active:text-sky-200">v{{ $entry['version'] }}</a></li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                <ol class="relative max-w-3xl space-y-12 border-l border-white/[.08] pl-7 sm:pl-10 {{ count($entries) > 1 ? '' : 'lg:col-span-2' }}">
                    @foreach ($entries as $entry)
                        <li id="{{ $entry['anchor'] }}" data-reveal class="relative scroll-mt-28">
                            <span class="absolute -left-[2.06rem] top-2 h-2.5 w-2.5 rounded-full bg-sky-400 ring-4 ring-ink-900 sm:-left-[2.82rem]" aria-hidden="true"></span>
                            <div class="flex flex-wrap items-baseline gap-x-4 gap-y-1">
                                <h2 class="font-mono text-xl font-semibold text-white">
                                    <a href="#{{ $entry['anchor'] }}" class="hover:text-sky-200">v{{ $entry['version'] }}</a>
                                </h2>
                                <time class="text-sm text-slate-500" datetime="{{ $entry['date'] }}">{{ $date($entry['date']) }}</time>
                            </div>
                            <ul class="ss-panel mt-5 divide-y divide-white/[.06]">
                                @foreach ($entry['items'] as [$type, $text])
                                    <li class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-start sm:gap-4">
                                        <span class="ss-badge {{ $badge[$type] ?? $badge['new'] }} w-fit shrink-0 sm:w-24 sm:justify-center">{{ $badgeLabels[$type] ?? $type }}</span>
                                        <span class="text-[15px] leading-relaxed text-slate-300">{{ $text }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ol>
            </div>
        @endif
    </section>
@endsection
