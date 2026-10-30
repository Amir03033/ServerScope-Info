@extends('layouts.app')

@php
    $lang = ($locale ?? app()->getLocale()) === 'en' ? 'en' : 'nl';
    $t = fn (string $key) => trans('site.waitlist.' . $key, [], $lang);
    $plans = $plans ?? [];
    $selected = old('plan', $selected ?? '');
@endphp

@section('title', $t('title'))
@section('description', $t('intro'))

@section('content')
    <section class="ss-container max-w-2xl pt-16 sm:pt-24">
        <x-page-header :title="$t('headline')" :intro="$t('intro')" />

        @if (session('waitlist-removed'))
            <p role="status" class="mt-8 rounded-xl border border-line bg-surface p-4 text-sm text-fg">{{ $t('removed') }}</p>
        @endif

        @if (session('waitlist-done'))
            <div role="status" class="ss-card mt-8 p-6">
                <p class="flex items-center gap-2 font-semibold text-fg"><x-ss-icon name="check" class="h-5 w-5 text-ok" /> {{ $t('done') }}</p>
                @if (session('waitlist-remove-url'))
                    <p class="mt-4 text-sm text-muted">{{ $t('remove_link') }}</p>
                    <p class="mt-1 break-all font-mono text-xs text-fg">{{ session('waitlist-remove-url') }}</p>
                    <button type="button" class="ss-btn ss-btn-secondary mt-3 !min-h-8 !py-1 !text-xs" data-copy-text="{{ session('waitlist-remove-url') }}"
                            data-copy-label="{{ trans('site.docs.copy', [], $lang) }}" data-copied-label="{{ trans('site.docs.copied', [], $lang) }}">
                        <x-ss-icon name="copy" class="h-3.5 w-3.5" /><span>{{ trans('site.docs.copy', [], $lang) }}</span>
                    </button>
                @endif
                <p class="mt-4 text-xs text-subtle">{{ $t('remove_contact') }} <a class="text-accent underline" href="{{ url($lang . '/contact') }}">{{ trans('site.nav.contact', [], $lang) }}</a></p>
            </div>
        @else
            @if ($errors->any())
                <div role="alert" class="mt-8 rounded-xl border border-bad/40 bg-surface p-4 text-sm text-bad">{{ trans('site.contact.errors', [], $lang) }}</div>
            @endif

            <form method="POST" action="{{ url($lang . '/waitlist') }}" class="relative mt-8 space-y-5" novalidate>
                @csrf
                <x-honeypot />

                <x-form-field name="email" type="email" :label="$t('email')" />

                @if ($plans !== [])
                    <div>
                        <label for="plan" class="text-sm font-medium text-fg">{{ $t('plan') }}</label>
                        <select id="plan" name="plan" class="ss-input mt-2">
                            <option value="">{{ $t('plan_none') }}</option>
                            @foreach ($plans as $key => $name)
                                <option value="{{ $key }}" @selected($selected === $key)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div>
                    <label class="flex items-start gap-3 text-sm text-fg">
                        <input type="checkbox" name="consent" value="1" required class="mt-1 h-4 w-4 shrink-0 accent-[var(--ss-accent)]"
                               @checked(old('consent')) @error('consent') aria-invalid="true" aria-describedby="consent-error" @enderror>
                        <span>{{ $t('consent') }}</span>
                    </label>
                    @error('consent')<p id="consent-error" class="mt-1.5 text-sm text-bad">{{ $message }}</p>@enderror
                </div>

                <x-button type="submit">{{ $t('submit') }}</x-button>
                <p class="text-xs text-subtle">{{ $t('privacy') }}</p>
            </form>
        @endif
    </section>
@endsection
