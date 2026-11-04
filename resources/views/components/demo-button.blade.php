{{-- "Try the demo": only rendered while the demo is switched on in Site-instellingen. --}}
@props(['locale' => 'nl', 'variant' => 'ghost'])
@php
    $demoUrl = app(\App\Services\Site\SiteData::class)->demoUrl();
@endphp
@if ($demoUrl)
    <x-button :href="$demoUrl" :variant="$variant" rel="nofollow" data-demo-button {{ $attributes }}>
        <x-ss-icon name="play" class="h-4 w-4" /> {{ trans('site.common.demo', [], $locale) }}
    </x-button>
@endif
