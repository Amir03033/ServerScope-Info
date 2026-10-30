<?php

namespace Tests\Feature;

use App\Models\LegalDocument;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\PublishesLegalDocuments;
use Tests\TestCase;

/**
 * Checks the JSON-LD of every public page against the schema.org vocabulary
 * (tests/Fixtures/schemaorg, see the README there for source and version).
 */
class StructuredDataTest extends TestCase
{
    use PublishesLegalDocuments;
    use RefreshDatabase;

    /** Not schema.org, but the documented Google form for SearchAction. */
    private const ALLOWED_EXTRA = ['query-input'];

    /** @var array<string, list<string>> class => direct parents */
    private static array $classes = [];

    /** @var array<string, list<string>> property => classes in domainIncludes */
    private static array $properties = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
        config(['serverscope.noindex' => false]);

        if (self::$classes === []) {
            $graph = json_decode(file_get_contents(base_path('tests/Fixtures/schemaorg/schemaorg-current-https.jsonld')), true)['@graph'];
            $ids = fn ($value) => array_map(fn ($v) => substr($v['@id'], 7), isset($value['@id']) ? [$value] : ($value ?? []));

            foreach ($graph as $node) {
                $types = (array) $node['@type'];
                $name = substr($node['@id'], 7);

                if (in_array('rdfs:Class', $types, true)) {
                    self::$classes[$name] = $ids($node['rdfs:subClassOf'] ?? null);
                }

                if (in_array('rdf:Property', $types, true)) {
                    self::$properties[$name] = $ids($node['schema:domainIncludes'] ?? null);
                }
            }
        }
    }

    /** @return list<string> the class and all its ancestors */
    private function lineage(string $class): array
    {
        $all = [$class];

        foreach (self::$classes[$class] ?? [] as $parent) {
            $all = [...$all, ...$this->lineage($parent)];
        }

        return array_values(array_unique($all));
    }

    /**
     * @return list<string> problems, empty when valid
     */
    private function validate(array $node, string $where): array
    {
        $errors = [];
        $type = $node['@type'] ?? null;

        if ($type === null) {
            // A reference to a node elsewhere in the graph.
            return array_keys($node) === ['@id'] ? [] : ["{$where}: node without @type"];
        }

        if (! isset(self::$classes[$type])) {
            return ["{$where}: unknown type {$type}"];
        }

        $lineage = $this->lineage($type);

        foreach ($node as $property => $value) {
            if (str_starts_with($property, '@') || in_array($property, self::ALLOWED_EXTRA, true)) {
                continue;
            }

            if (! isset(self::$properties[$property])) {
                $errors[] = "{$where}: unknown property {$type}.{$property}";
            } elseif (array_intersect(self::$properties[$property], $lineage) === []) {
                $errors[] = "{$where}: {$property} is not a property of {$type}";
            }

            foreach (is_array($value) && array_is_list($value) ? $value : [$value] as $item) {
                if (is_array($item)) {
                    $errors = [...$errors, ...$this->validate($item, "{$where} > {$property}")];
                }
            }
        }

        return $errors;
    }

    /**
     * @return list<array<string, mixed>> the @graph nodes of a page
     */
    private function graph(string $path): array
    {
        $html = $this->get($path)->assertOk()->getContent();
        $this->assertSame(1, preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m), "{$path}: one JSON-LD block");
        $data = json_decode($m[1][0], true, flags: JSON_THROW_ON_ERROR);
        $this->assertSame('https://schema.org', $data['@context']);

        return $data['@graph'];
    }

    public function test_the_json_ld_of_every_public_page_is_valid_schema_org(): void
    {
        $this->fillCompany();
        Setting::put('company.same_as', "https://github.com/example\nnot a url");
        foreach (array_keys(LegalDocument::DOCUMENTS) as $key) {
            $this->publish($key, 'nl');
        }

        preg_match_all('#<loc>([^<]+)</loc>#', $this->get('/sitemap.xml')->getContent(), $locs);
        $types = [];

        foreach (array_unique($locs[1]) as $url) {
            $path = parse_url($url, PHP_URL_PATH);

            foreach ($this->graph($path) as $i => $node) {
                $this->assertSame([], $this->validate($node, "{$path} #{$i}"));
                $types[$node['@type']] = true;
                $this->assertArrayNotHasKey('offers', $node, 'no prices in structured data');
                $this->assertArrayNotHasKey('aggregateRating', $node, 'no ratings in structured data');
                $this->assertArrayNotHasKey('review', $node, 'no reviews in structured data');
            }
        }

        $this->assertEqualsCanonicalizing(['Organization', 'WebSite', 'SoftwareApplication', 'BreadcrumbList', 'TechArticle', 'FAQPage'], array_keys($types));
    }

    public function test_the_validator_itself_rejects_wrong_markup(): void
    {
        $this->assertNotSame([], $this->validate(['@type' => 'Organization', 'headline' => 'x'], 'test'));
        $this->assertNotSame([], $this->validate(['@type' => 'Organisation'], 'test'));
        $this->assertNotSame([], $this->validate(['@type' => 'WebSite', 'potentialAction' => ['@type' => 'SearchAction', 'madeUp' => 1]], 'test'));
        $this->assertSame([], $this->validate(['@type' => 'TechArticle', 'headline' => 'x', 'publisher' => ['@id' => 'y']], 'test'));
    }

    public function test_organization_only_shows_company_details_that_are_filled_in(): void
    {
        $organization = collect($this->graph('/nl'))->firstWhere('@type', 'Organization');
        $this->assertSame(['@type', '@id', 'name', 'url', 'logo'], array_keys($organization));
        $this->assertFileExists(public_path('images/logo.png'));

        $this->fillCompany();
        Setting::put('company.same_as', "https://github.com/example\nnot a url\nhttp://insecure.example");
        $organization = collect($this->graph('/nl'))->firstWhere('@type', 'Organization');

        $this->assertSame('Waarde company.name', $organization['legalName']);
        $this->assertSame('PostalAddress', $organization['address']['@type']);
        $this->assertSame(['https://github.com/example'], $organization['sameAs']);
    }

    public function test_breadcrumbs_and_tech_article_match_the_visible_page(): void
    {
        $graph = collect($this->graph('/en/docs/getting-started'));

        $crumbs = $graph->firstWhere('@type', 'BreadcrumbList')['itemListElement'];
        $this->assertSame([1, 2, 3], array_column($crumbs, 'position'));
        $this->assertSame(url('/en/docs'), $crumbs[1]['item']);
        $this->assertSame(url('/en/docs/getting-started'), $crumbs[2]['item']);

        $article = $graph->firstWhere('@type', 'TechArticle');
        $this->assertSame('en', $article['inLanguage']);
        $this->get('/en/docs/getting-started')->assertSee($article['headline']);

        $search = collect($this->graph('/en'))->firstWhere('@type', 'WebSite')['potentialAction']['target']['urlTemplate'];
        $this->assertSame(url('/en/docs').'?q={search_term_string}', $search);
        $this->get('/en/docs?q=agent')->assertOk()->assertSee('agent');
    }
}
