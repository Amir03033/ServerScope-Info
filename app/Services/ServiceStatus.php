<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Live status of ServerScope itself, read from a public status page of the
 * ServerScope application ({app_url}/status/{slug}.json).
 *
 * Only the documented fields are passed on, and only known states are
 * accepted. Nothing is ever invented: without a configured page, or when
 * the source cannot be reached, the result says so.
 */
class ServiceStatus
{
    public const STATES = ['operational', 'degraded', 'partial_outage', 'major_outage', 'maintenance', 'unknown'];

    public const INCIDENT_STATES = ['detected', 'identified', 'investigating', 'resolved'];

    /**
     * @return array{state: 'not_configured'|'unavailable'|'ok', page_url?: string, data?: array}
     */
    public function current(): array
    {
        $slug = config('serverscope.status_page');

        if (! is_string($slug) || ! preg_match('/\A[a-z0-9-]+\z/', $slug)) {
            return ['state' => 'not_configured'];
        }

        $pageUrl = config('serverscope.app_url').'/status/'.$slug;
        $ttl = max(10, (int) config('serverscope.status_cache_seconds', 60));

        $data = Cache::remember("service-status:{$slug}", $ttl, fn () => $this->fetch($pageUrl.'.json') ?? false);

        if ($data === false) {
            return ['state' => 'unavailable', 'page_url' => $pageUrl];
        }

        return ['state' => 'ok', 'page_url' => $pageUrl, 'data' => $data];
    }

    private function fetch(string $url): ?array
    {
        try {
            $response = Http::acceptJson()->timeout(4)->connectTimeout(3)->withoutRedirecting()->get($url);
        } catch (Throwable $exception) {
            Log::warning('Service status could not be loaded', ['error' => $exception->getMessage()]);

            return null;
        }

        if (! $response->successful() || ! is_array($response->json())) {
            Log::warning('Service status could not be loaded', ['status' => $response->status()]);

            return null;
        }

        return $this->normalise($response->json());
    }

    /**
     * Keep only the expected fields with the expected types.
     */
    public function normalise(array $raw): ?array
    {
        if (! isset($raw['overall'], $raw['components']) || ! is_array($raw['components'])) {
            return null;
        }

        $state = fn ($value) => in_array($value, self::STATES, true) ? $value : 'unknown';
        $string = fn ($value, int $max = 200) => is_string($value) && $value !== '' ? mb_substr($value, 0, $max) : null;
        $date = fn ($value) => is_string($value) && strtotime($value) !== false ? $value : null;

        $components = [];

        foreach (array_slice($raw['components'], 0, 50) as $component) {
            if (! is_array($component) || $string($component['name'] ?? null) === null) {
                continue;
            }

            $uptime = null;

            if (isset($component['uptime']) && is_array($component['uptime'])) {
                $uptime = [];

                foreach (array_slice($component['uptime'], -90) as $day) {
                    if (is_array($day) && $date($day['date'] ?? null)) {
                        $value = $day['uptime'] ?? null;
                        $uptime[] = ['date' => $day['date'], 'uptime' => is_numeric($value) ? max(0.0, min(100.0, (float) $value)) : null];
                    }
                }
            }

            $components[] = [
                'name' => $string($component['name']),
                'description' => $string($component['description'] ?? null, 300),
                'state' => $state($component['state'] ?? null),
                'uptime' => $uptime,
            ];
        }

        $incidents = [];

        foreach (array_slice(is_array($raw['incidents'] ?? null) ? $raw['incidents'] : [], 0, 20) as $incident) {
            if (! is_array($incident) || $string($incident['title'] ?? null) === null || ! $date($incident['started_at'] ?? null)) {
                continue;
            }

            $incidents[] = [
                'title' => $string($incident['title']),
                'component' => $string($incident['component'] ?? null),
                'severity' => in_array($incident['severity'] ?? null, ['warning', 'critical'], true) ? $incident['severity'] : 'warning',
                'status' => in_array($incident['status'] ?? null, self::INCIDENT_STATES, true) ? $incident['status'] : 'detected',
                'started_at' => $incident['started_at'],
                'resolved_at' => $date($incident['resolved_at'] ?? null),
            ];
        }

        return [
            'name' => $string($raw['name'] ?? null),
            'overall' => $state($raw['overall']),
            'components' => $components,
            'incidents' => $incidents,
            'generated_at' => $date($raw['generated_at'] ?? null),
        ];
    }
}
