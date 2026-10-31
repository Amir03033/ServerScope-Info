@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key, array $replace = []) => trans('site.legal.' . $key, $replace, $lang);
@endphp

@section('title', $t($page . '.title') . ' — ServerScope')
@section('description', $t($page . '.intro'))
@if ($incomplete)
    @section('noindex', '1')
@endif

@section('content')
    <section class="ss-container max-w-3xl pt-16 sm:pt-24">
        <x-page-header :title="$t($page . '.title')" :intro="$t($page . '.intro')" />

        @if ($incomplete)
            <div role="alert" data-testid="legal-draft" class="mt-8 rounded-xl border border-line bg-surface p-4 text-sm text-fg">{{ $t('draft_warning') }}</div>
        @endif

        <p class="mt-6 text-sm text-subtle">{{ $t('updated', ['date' => $updated]) }}</p>

        <x-prose :html="$html" class="mt-10" />
    </section>
@endsection
