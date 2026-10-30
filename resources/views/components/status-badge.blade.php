{{--
    Live ServerScope status from the server-side cache. Never "operational"
    unless the public status page says so. When nothing is cached yet,
    app.js asks this site's own /status.json (never ServerScope directly).
--}}
@props(['locale' => 'nl', 'compact' => false])
@php
    $service = app(\App\Services\Site\StatusService::class);
    $status = $service->cached();
    $state = $status['state'] ?? 'unknown';
    $dot = match ($state) {
        'operational' => 'ss-dot-ok',
        'degraded', 'maintenance', 'partial_outage' => 'ss-dot-warn',
        'major_outage' => 'ss-dot-bad',
        default => '',
    };
@endphp
<a href="{{ url($locale . '/status') }}"
   {{ $attributes->merge(['class' => 'ss-badge hover:border-line-strong transition-colors']) }}
   data-status-badge @if ($status === null) data-status-pending @endif
   data-labels='@json(collect(\App\Services\Site\StatusService::STATES)->mapWithKeys(fn ($s) => [$s => trans('site.status2.'.$s, [], $locale)]))'>
    <span class="ss-dot {{ $dot }} {{ $state === 'operational' ? 'ss-live-dot' : '' }}" data-status-dot aria-hidden="true"></span>
    <span data-status-text>{{ trans('site.status2.' . $state, [], $locale) }}</span>
</a>
