@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.status.' . $key, [], $lang);
@endphp

@section('title', $t('title'))

@section('content')
    <section class="mx-auto max-w-3xl px-4 pb-4 pt-20 sm:px-6">
        <x-page-header :title="$t('headline')" :intro="$t('intro')" intro-class="" />

        <div class="mt-10 flex items-center gap-3 rounded-2xl border border-emerald-400/20 bg-emerald-400/10 p-5">
            <span class="relative flex h-3 w-3" aria-hidden="true">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-400"></span>
            </span>
            <p class="font-semibold text-emerald-200">{{ $t('all_ok') }}</p>
        </div>

        <ul class="mt-6 divide-y divide-white/5 overflow-hidden rounded-2xl border border-white/10 bg-slate-900/50">
            @foreach ($t('services') as [$name, $state])
                <li class="flex items-center justify-between px-5 py-4">
                    <span class="font-medium text-white">{{ $name }}</span>
                    <span class="flex items-center gap-2 text-sm text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400" aria-hidden="true"></span> {{ $state }}
                    </span>
                </li>
            @endforeach
        </ul>
        <p class="mt-3 text-xs text-slate-500">{{ $t('note') }}</p>

        <h2 class="mt-14 text-xl font-bold text-white">{{ $t('history') }}</h2>
        <p class="mt-3 rounded-2xl border border-dashed border-white/10 p-6 text-center text-slate-500">{{ $t('none') }}</p>
    </section>
@endsection
