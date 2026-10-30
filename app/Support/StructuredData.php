<?php

namespace App\Support;

use App\Services\Legal\CompanyData;
use Illuminate\Support\Carbon;

/**
 * schema.org JSON-LD for the public pages. Only facts that are also visible
 * on the site or entered in the admin panel: no prices, offers, ratings or
 * reviews. Company details appear only once they are filled in.
 */
class StructuredData
{
    public static function organization(): array
    {
        $company = app(CompanyData::class);
        $data = [
            '@type' => 'Organization',
            '@id' => url('/#organization'),
            'name' => 'ServerScope',
            'url' => url('/'),
            'logo' => url('/images/logo.png'),
        ];

        if (filled($company->get('company.name'))) {
            $data['legalName'] = $company->get('company.name');
        }

        if (filled($company->get('company.email'))) {
            $data['email'] = $company->get('company.email');
        }

        if (filled($company->get('company.vat'))) {
            $data['vatID'] = $company->get('company.vat');
        }

        if (filled($company->get('company.street')) && filled($company->get('company.city'))) {
            $data['address'] = array_filter([
                '@type' => 'PostalAddress',
                'streetAddress' => $company->get('company.street'),
                'postalCode' => $company->get('company.postcode'),
                'addressLocality' => $company->get('company.city'),
                'addressCountry' => $company->get('company.country'),
            ], 'filled');
        }

        $sameAs = collect(preg_split('/\R/', (string) $company->get('company.same_as')))
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => filter_var($line, FILTER_VALIDATE_URL) && str_starts_with($line, 'https://'))
            ->values()->all();

        if ($sameAs !== []) {
            $data['sameAs'] = $sameAs;
        }

        return $data;
    }

    public static function website(string $locale): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => url('/#website'),
            'name' => 'ServerScope',
            'url' => url('/'),
            'inLanguage' => ['nl', 'en'],
            'publisher' => ['@id' => url('/#organization')],
            // The documentation search really exists at this address.
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => ['@type' => 'EntryPoint', 'urlTemplate' => url($locale.'/docs').'?q={search_term_string}'],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    /**
     * The product itself. Deliberately without offers or aggregateRating:
     * there are no public prices or reviews yet.
     */
    public static function software(string $locale): array
    {
        return [
            '@type' => 'SoftwareApplication',
            '@id' => url('/#software'),
            'name' => 'ServerScope',
            'url' => url($locale),
            'description' => trans('site.meta.description', [], $locale),
            'applicationCategory' => 'DeveloperApplication',
            'operatingSystem' => 'Web',
            'inLanguage' => ['nl', 'en'],
            'publisher' => ['@id' => url('/#organization')],
        ];
    }

    /**
     * @param  list<array{0: string, 1: ?string}>  $items  [name, url]; the last one is the current page
     */
    public static function breadcrumbs(array $items): array
    {
        $list = [];
        $last = count($items) - 1;

        foreach ($items as $i => [$name, $url]) {
            // Steps without their own page are only shown, not listed.
            if ($url === null && $i !== $last) {
                continue;
            }

            $list[] = array_filter([
                '@type' => 'ListItem',
                'position' => count($list) + 1,
                'name' => $name,
                'item' => $url ?? url()->current(),
            ]);
        }

        return ['@type' => 'BreadcrumbList', 'itemListElement' => $list];
    }

    public static function techArticle(string $headline, ?string $description, string $locale, ?string $modified): array
    {
        return array_filter([
            '@type' => 'TechArticle',
            'headline' => $headline,
            'description' => $description,
            'inLanguage' => $locale,
            'url' => url()->current(),
            'mainEntityOfPage' => url()->current(),
            'dateModified' => $modified ? Carbon::parse($modified)->toAtomString() : null,
            'author' => ['@id' => url('/#organization')],
            'publisher' => ['@id' => url('/#organization')],
            'isPartOf' => ['@id' => url('/#website')],
            'about' => ['@id' => url('/#software')],
        ], fn ($value) => $value !== null);
    }

    /**
     * Only for questions and answers that are visible on the page.
     *
     * @param  list<array{question: string, answer: string}>  $items
     */
    public static function faq(array $items): array
    {
        return [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item) => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['answer']],
            ], $items),
        ];
    }

    /**
     * Collect a node while the page renders; the layout prints them all.
     * Kept on the request, so nothing leaks into the next one.
     */
    public static function push(array $node): void
    {
        $nodes = request()->attributes->get('structured_data', []);
        $nodes[] = $node;
        request()->attributes->set('structured_data', $nodes);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public static function collected(): array
    {
        return request()->attributes->get('structured_data', []);
    }

    /**
     * One <script> with an @graph, safe to print in HTML.
     *
     * @param  list<array<string, mixed>>  $nodes
     */
    public static function script(array $nodes): string
    {
        $json = json_encode(['@context' => 'https://schema.org', '@graph' => array_values($nodes)],
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP);

        return '<script type="application/ld+json">'.$json.'</script>';
    }
}
