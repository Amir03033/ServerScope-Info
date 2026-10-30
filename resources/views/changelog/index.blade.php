@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.changelog.' . $key, [], $lang);

    // Release data lives in lang/{nl,en}/changelog.php (newest first). A controller may still pass $entries to override it.
    $entries = $entries ?? trans('changelog.entries', [], $lang);
    $entries = is_array($entries) ? $entries : [];

    $badge = [
        'new' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-300',
        'improved' => 'border-sky-400/20 bg-sky-400/10 text-sky-300',
        'fixed' => 'border-amber-400/20 bg-amber-400/10 text-amber-300',
    ];
    $badgeLabels = $t('badges');
@endphp

@section('title', $t('title'))

@section('content')
    <section class="mx-auto max-w-3xl px-4 pt-20 sm:px-6">
        <x-page-header :title="$t('headline')" :intro="$t('intro')" intro-class="" />

        <ol class="relative mt-14 space-y-10 border-l border-white/10 pl-8">
            @foreach ($entries as $entry)
                <li data-reveal class="relative">
                    <span class="absolute -left-[2.45rem] top-1.5 h-3 w-3 rounded-full border-2 border-[#0b1220] bg-sky-400" aria-hidden="true"></span>
                    <div class="flex flex-wrap items-baseline gap-x-3">
                        <h2 class="text-xl font-bold text-white" id="{{ $entry['id'] ?? '' }}">{{ filled($entry['title'] ?? null) ? $entry['title'] : 'v'.$entry['version'] }}</h2>
                        <time class="text-sm text-slate-500" datetime="{{ $entry['date'] }}">{{ \Illuminate\Support\Carbon::parse($entry['date'])->locale($lang)->translatedFormat('j F Y') }}</time>
                    </div>
                    @if (filled($entry['html'] ?? null))
                        <x-prose :html="$entry['html']" class="ss-docs mt-4" />
                    @endif
                    <ul class="mt-4 space-y-3">
                        @foreach ($entry['items'] as [$type, $text])
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 shrink-0 rounded-md border px-2 py-0.5 text-xs font-semibold {{ $badge[$type] ?? $badge['new'] }}">{{ $badgeLabels[$type] ?? $type }}</span>
                                <span class="text-slate-300">{{ $text }}</span>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endforeach
        </ol>
    </section>
@endsection
