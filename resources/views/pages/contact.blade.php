@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.contact.' . $key, [], $lang);
    $available = $available ?? filled(config('serverscope.contact_recipient'));
@endphp

@section('title', $t('title'))
@section('description', $t('description'))

@section('content')
    <section class="ss-container grid gap-12 pt-16 sm:pt-20 lg:grid-cols-[1.35fr_1fr] lg:gap-16">
        <div>
            <x-page-header :eyebrow="$t('eyebrow')" :title="$t('headline')" :intro="$t('intro')" intro-class="max-w-lg" />

            @if (session('success'))
                <div role="status" class="mt-8 flex items-start gap-3 rounded-xl border border-emerald-400/30 bg-emerald-400/10 p-4 text-sm text-emerald-100">
                    <x-ss-icon name="check" class="mt-0.5 h-5 w-5 shrink-0 text-emerald-300" /> <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('failure'))
                <div role="alert" class="mt-8 flex items-start gap-3 rounded-xl border border-rose-400/30 bg-rose-400/10 p-4 text-sm text-rose-100">
                    <x-ss-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-rose-300" /> <span>{{ session('failure') }}</span>
                </div>
            @elseif ($errors->any())
                <div role="alert" class="mt-8 flex items-start gap-3 rounded-xl border border-rose-400/30 bg-rose-400/10 p-4 text-sm text-rose-100">
                    <x-ss-icon name="alert" class="mt-0.5 h-5 w-5 shrink-0 text-rose-300" /> <span>{{ $t('errors') }}</span>
                </div>
            @endif

            @if ($available)
                <form method="POST" action="{{ url($lang . '/contact') }}" class="mt-8 space-y-5" novalidate data-submit-once>
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <x-form-field name="name" :label="$t('name')" maxlength="100" />
                        <x-form-field name="email" type="email" :label="$t('email')" maxlength="254" />
                    </div>
                    <x-form-field name="subject" :label="$t('subject')" autocomplete="off" maxlength="150" :value="$subject ?? null" />
                    <x-form-field name="message" :label="$t('message')" rows="7" maxlength="5000" :hint="$t('message_hint')" />

                    {{-- Honeypot: hidden from people and assistive technology, tempting for bots. --}}
                    <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                        <label for="website">{{ $t('honeypot') }}</label>
                        <input id="website" type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="flex flex-col gap-4 pt-1 sm:flex-row sm:items-center">
                        <x-button type="submit" size="lg">
                            <span data-idle-label>{{ $t('send') }}</span>
                            <span data-busy-label hidden>{{ $t('sending') }}</span>
                        </x-button>
                        <p class="text-[13px] text-slate-500">{{ $t('privacy') }}</p>
                    </div>
                </form>
            @else
                <div class="mt-8 rounded-2xl border border-white/10 bg-white/[.02] p-6">
                    <p class="flex items-center gap-2 font-semibold text-white"><x-ss-icon name="info" class="h-5 w-5 text-sky-300" /> {{ $t('unavailable_title') }}</p>
                    <p class="mt-2 text-[15px] leading-relaxed text-slate-400">{{ $t('unavailable_text') }}</p>
                    <x-button :href="url($lang . '/docs')" variant="secondary" size="sm" class="mt-5" :arrow="true">{{ $t('docs') }}</x-button>
                </div>
            @endif
        </div>

        <aside class="grid h-fit gap-4 lg:mt-24">
            <div class="ss-panel p-6">
                <x-icon-tile name="book" />
                <h2 class="mt-5 font-semibold text-white">{{ $t('aside_title') }}</h2>
                <p class="mt-2 text-[15px] leading-relaxed text-slate-400">{{ $t('aside') }}</p>
                <x-button :href="url($lang . '/docs')" variant="secondary" size="sm" class="mt-5">{{ $t('docs') }}</x-button>
            </div>
            <div class="ss-panel p-6">
                <x-icon-tile name="shield" />
                <h2 class="mt-5 font-semibold text-white">{{ $t('security_title') }}</h2>
                <p class="mt-2 text-[15px] leading-relaxed text-slate-400">{{ $t('security_text') }}</p>
                <x-button :href="url($lang . '/security')" variant="secondary" size="sm" class="mt-5">{{ $t('security_cta') }}</x-button>
            </div>
        </aside>
    </section>
@endsection
