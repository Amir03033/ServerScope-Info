<?php

namespace App\Services\Docs;

use App\Models\DocCategory;

/**
 * Default documentation categories. The order keeps the reading order of
 * the original eight guides unchanged.
 */
class DocCategories
{
    /** translation_key => [sort, icon, nl, en] */
    public const DEFAULTS = [
        'start' => [10, 'book', 'Aan de slag', 'Getting started'],
        'servers' => [20, 'server', 'Servers en agent', 'Servers and agent'],
        'monitoring' => [30, 'pulse', 'Monitoring', 'Monitoring'],
        'alerts' => [40, 'bell', 'Meldingen en statuspagina\'s', 'Alerts and status pages'],
        'api' => [50, 'code', 'API en integraties', 'API and integrations'],
        'account' => [60, 'shield', 'Account, teams en beveiliging', 'Account, teams and security'],
    ];

    /** Category of each original guide. */
    public const ORIGINAL = [
        'getting-started' => 'start',
        'server-monitoring' => 'monitoring',
        'domain-monitoring' => 'monitoring',
        'alerts-incidents' => 'alerts',
        'discord-notifications' => 'alerts',
        'status-pages' => 'alerts',
        'api-webhooks' => 'api',
        'security-account' => 'account',
    ];

    /**
     * Creates missing default categories; never changes existing ones.
     */
    public function ensure(): void
    {
        foreach (self::DEFAULTS as $key => [$sort, $icon, $nl, $en]) {
            foreach (['nl' => $nl, 'en' => $en] as $locale => $name) {
                DocCategory::query()->firstOrCreate(
                    ['locale' => $locale, 'translation_key' => $key],
                    ['name' => $name, 'slug' => $key, 'icon' => $icon, 'sort' => $sort],
                );
            }
        }
    }

    public function id(string $locale, string $key): ?int
    {
        return DocCategory::query()->where('locale', $locale)->where('translation_key', $key)->value('id');
    }
}
