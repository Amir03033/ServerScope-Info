<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Known AI crawlers and whether robots.txt lets them in (Instellingen → Site
 * → AI-crawlers). All are allowed by default: the owner wants AI models to
 * know ServerScope. robots.txt is a request, not a lock; well-behaved
 * crawlers follow it.
 */
class Crawlers
{
    /** user agent => [operator, kind, what it does] */
    public const LIST = [
        'OAI-SearchBot' => ['OpenAI', 'search', 'Zoekresultaten in ChatGPT'],
        'ChatGPT-User' => ['OpenAI', 'user', 'Haalt een pagina op als een gebruiker daarom vraagt'],
        'GPTBot' => ['OpenAI', 'training', 'Training van OpenAI-modellen'],
        'Claude-SearchBot' => ['Anthropic', 'search', 'Zoekresultaten in Claude'],
        'Claude-User' => ['Anthropic', 'user', 'Haalt een pagina op als een gebruiker daarom vraagt'],
        'ClaudeBot' => ['Anthropic', 'training', 'Training van Claude-modellen'],
        'PerplexityBot' => ['Perplexity', 'search', 'Zoekresultaten in Perplexity'],
        'Perplexity-User' => ['Perplexity', 'user', 'Haalt een pagina op als een gebruiker daarom vraagt'],
        'Google-Extended' => ['Google', 'training', 'Gebruik voor Gemini-training (los van Google Zoeken)'],
        'Applebot-Extended' => ['Apple', 'training', 'Gebruik voor Apple-modellen (los van Siri/Spotlight-zoeken)'],
        'CCBot' => ['Common Crawl', 'training', 'Openbare webarchieven, veel gebruikt voor training'],
    ];

    public const KINDS = [
        'search' => 'Zoeken',
        'user' => 'Op verzoek van een gebruiker',
        'training' => 'Training',
    ];

    /** Never for any crawler: the admin panel, previews and personal removal links. */
    public const ALWAYS_DISALLOWED = ['/admin', '/admin-preview/', '/nl/waitlist/remove/', '/en/waitlist/remove/'];

    public static function allowed(string $agent): bool
    {
        $settings = (array) Setting::get('crawlers', []);

        return (bool) ($settings[$agent] ?? true);
    }

    public static function robots(): string
    {
        if (config('serverscope.noindex')) {
            return "# Not indexed (staging or test)\nUser-agent: *\nDisallow: /\n";
        }

        $rules = implode('', array_map(fn (string $path) => "Disallow: {$path}\n", self::ALWAYS_DISALLOWED));
        $out = "User-agent: *\n{$rules}";

        foreach (self::LIST as $agent => [$operator, $kind]) {
            $out .= "\n# {$operator} ({$kind})\nUser-agent: {$agent}\n";
            $out .= self::allowed($agent) ? $rules : "Disallow: /\n";
        }

        return $out."\n# Summary for AI tools: ".url('/llms.txt')."\nSitemap: ".url('/sitemap.xml')."\n";
    }
}
