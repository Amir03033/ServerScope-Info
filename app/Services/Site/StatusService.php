<?php

namespace App\Services\Site;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Live status from the public ServerScope status page ({url}.json),
 * fetched on the server only (visitors' browsers never talk to
 * ServerScope) and cached briefly.
 *
 * Anything other than a valid answer is "unknown": this site never shows
 * "operational" unless ServerScope itself says so.
 */
class StatusService
{
    public const STATES = ['operational', 'degraded', 'partial_outage', 'major_outage', 'maintenance', 'unknown'];

    public const CACHE_KEY = 'site-status';

    public function pageUrl(): string
    {
        return rtrim((string) Setting::get('status_page_url'), '/');
    }

    /**
     * Cached value only; never blocks on the network. Null = not fetched yet.
     *
     * @return array{state: string, name: ?string, components: array, incidents: array, generated_at: ?string, fetched_at: string}|null
     */
    public function cached(): ?array
    {
        return rescue(fn () => Cache::get(self::CACHE_KEY), null, report: false);
    }

    /**
     * Cached value, fetching it when it is missing (at most a few seconds).
     *
     * @return array{state: string, name: ?string, components: array, incidents: array, generated_at: ?string, fetched_at: string}
     */
    public function current(): array
    {
        if (($cached = $this->cached()) !== null) {
            return $cached;
        }

        $status = $this->fetch();

        // Good answers are kept for a minute, failures for 30 seconds.
        rescue(fn () => Cache::put(self::CACHE_KEY, $status, $status['state'] === 'unknown' ? 30 : 60), report: false);

        return $status;
    }

    public function fetch(): array
    {
        $unknown = ['state' => 'unknown', 'name' => null, 'components' => [], 'incidents' => [], 'generated_at' => null, 'fetched_at' => now()->toIso8601String()];
        $url = $this->pageUrl();

        if (! str_starts_with($url, 'https://')) {
            return $unknown;
        }

        try {
            $response = Http::acceptJson()->connectTimeout(2)->timeout(3)->withoutRedirecting()->get($url.'.json');
        } catch (\Throwable) {
            return $unknown;
        }

        $data = $response->successful() ? $response->json() : null;

        if (! is_array($data) || ! in_array($data['overall'] ?? null, self::STATES, true)) {
            return $unknown;
        }

        $components = collect(is_array($data['components'] ?? null) ? $data['components'] : [])
            ->filter(fn ($c) => is_array($c) && is_string($c['name'] ?? null))
            ->map(fn (array $c) => ['name' => mb_substr($c['name'], 0, 120), 'state' => in_array($c['state'] ?? null, self::STATES, true) ? $c['state'] : 'unknown'])
            ->take(50)->values()->all();

        $incidents = collect(is_array($data['incidents'] ?? null) ? $data['incidents'] : [])
            ->filter(fn ($i) => is_array($i))
            ->map(fn (array $i) => [
                'title' => is_string($i['title'] ?? null) ? mb_substr($i['title'], 0, 200) : null,
                'component' => is_string($i['component'] ?? null) ? mb_substr($i['component'], 0, 120) : null,
                'resolved' => ($i['status'] ?? null) === 'resolved',
                'started_at' => is_string($i['started_at'] ?? null) ? $i['started_at'] : null,
            ])
            ->take(20)->values()->all();

        return [
            'state' => $data['overall'],
            'name' => is_string($data['name'] ?? null) ? mb_substr($data['name'], 0, 120) : null,
            'components' => $components,
            'incidents' => $incidents,
            'generated_at' => is_string($data['generated_at'] ?? null) ? $data['generated_at'] : null,
            'fetched_at' => now()->toIso8601String(),
        ];
    }
}
