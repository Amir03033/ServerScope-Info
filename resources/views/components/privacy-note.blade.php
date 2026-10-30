{{-- One line with a link to the privacy statement, once it is published. --}}
@props(['locale' => 'nl'])
@php
    $privacy = rescue(fn () => \App\Models\LegalDocument::query()->where('key', 'privacy')->where('locale', $locale)->first(), null, report: false);
@endphp
@if ($privacy?->currentVersion())
    <p {{ $attributes->merge(['class' => 'text-xs text-subtle']) }}>
        {{ trans('site.legal.privacy_note', [], $locale) }} <a href="{{ url($privacy->path()) }}" class="text-accent underline">{{ trans('site.legal.privacy_link', [], $locale) }}</a>.
    </p>
@endif
