<?php

namespace App\Services;

use App\Enums\FeatureStatus;
use App\Models\Feature;
use App\Models\LegalDocument;
use App\Services\Legal\LegalRenderer;
use App\Support\Markdown\MarkdownRenderer;

/**
 * Plain Markdown of the public pages (the .md versions) and /llms.txt and
 * /llms-full.txt. Only what is public on the site: visible docs, published
 * legal versions and features with status "beschikbaar". Docs about a
 * feature that is not available yet are left out, so an AI model does not
 * describe it as existing.
 */
class MarkdownExport
{
    public function __construct(
        private DocumentationService $documentation,
        private MarkdownRenderer $markdown,
        private LegalRenderer $legal,
    ) {}

    /**
     * @return list<array<string, mixed>> visible docs about available functionality
     */
    public function docs(string $locale): array
    {
        return array_values(array_filter($this->documentation->all($locale), fn (array $doc) => ! $doc['coming_soon']));
    }

    public function doc(string $locale, string $slug): ?string
    {
        $article = $this->documentation->find($locale, $slug);

        if ($article === null) {
            return null;
        }

        $body = trim($this->markdown->prepare((string) $article['content'], $locale));

        if (! str_starts_with($body, '# ')) {
            $body = "# {$article['title']}\n\n{$body}";
        }

        if (filled($article['description'])) {
            $body = preg_replace('/\A(# [^\n]*\n)/', "$1\n> {$article['description']}\n", $body, 1);
        }

        if ($article['coming_soon']) {
            $body .= "\n\n".($locale === 'en' ? '_This functionality is not available yet._' : '_Deze functionaliteit is nog niet beschikbaar._');
        }

        return $this->withSource($body, url("{$locale}/docs/{$slug}"), $locale);
    }

    public function legal(LegalDocument $document): ?string
    {
        $version = $document->currentVersion();

        if ($version === null) {
            return null;
        }

        $en = $document->locale === 'en';
        $meta = ($en ? 'Version' : 'Versie')." {$version->version}, ".($en ? 'effective from' : 'geldt vanaf').' '.$version->effective_at?->toDateString();

        return $this->withSource("# {$document->title}\n\n{$meta}\n\n".trim($this->legal->markdown($version)), url($document->path()), $document->locale);
    }

    /**
     * @return list<Feature>
     */
    public function availableFeatures(): array
    {
        return rescue(fn () => Feature::query()->where('status', FeatureStatus::Available->value)->orderBy('sort')->get()->all(), [], report: false);
    }

    public function llms(string $locale, bool $full = false): string
    {
        $en = $locale === 'en';
        $t = fn (string $key) => trans($key, [], $locale);
        $out = "# ServerScope\n\n> ".$t('site.meta.description')."\n\n";
        $out .= ($en
            ? "ServerScope is a web application for monitoring Linux servers (through a read-only agent), websites and domains. This file lists the public documentation of info.serverscope.nl. Only functionality that is available today is described. Every page is also available as Markdown (add .md).\n"
            : "ServerScope is een webapplicatie voor het monitoren van Linux-servers (via een agent die alleen leest), websites en domeinen. Dit bestand verwijst naar de openbare documentatie op info.serverscope.nl. Alleen functionaliteit die nu beschikbaar is, staat erin. Elke pagina is ook als Markdown beschikbaar (voeg .md toe).\n");

        $out .= "\n## ".($en ? 'Available functionality' : 'Beschikbare functionaliteit')."\n\n";
        foreach ($this->availableFeatures() as $feature) {
            $out .= '- '.($feature->name[$locale] ?? $feature->name['en'] ?? $feature->key).': '.($feature->summary[$locale] ?? '')."\n";
        }

        $docs = $this->docs($locale);
        $out .= "\n## ".($en ? 'Documentation' : 'Documentatie')."\n\n";
        foreach ($docs as $doc) {
            $out .= "- [{$doc['title']}](".url("{$locale}/docs/{$doc['slug']}.md").')'.(filled($doc['description']) ? ": {$doc['description']}" : '')."\n";
        }

        $legal = rescue(fn () => LegalDocument::query()->where('locale', $locale)->orderBy('sort')->get()->filter(fn (LegalDocument $d) => $d->currentVersion() !== null), collect(), report: false);
        if ($legal->isNotEmpty()) {
            $out .= "\n## ".($en ? 'Legal' : 'Juridisch')."\n\n";
            foreach ($legal as $document) {
                $out .= "- [{$document->title}](".url($document->path().'.md').')'.(filled($document->seo_description) ? ": {$document->seo_description}" : '')."\n";
            }
        }

        $out .= "\n## Optional\n\n";
        foreach (['pricing', 'changelog', 'status', 'contact'] as $page) {
            $out .= '- ['.$t("site.{$page}.headline").']('.url("{$locale}/{$page}").")\n";
        }
        $out .= '- ['.($en ? 'Dutch version' : 'English version').']('.url($en ? 'nl/llms.txt' : 'llms.txt').")\n";

        if ($full) {
            foreach ($docs as $doc) {
                $out .= "\n\n---\n\n".$this->doc($locale, $doc['slug']);
            }
            foreach ($legal as $document) {
                $out .= "\n\n---\n\n".$this->legal($document);
            }
        }

        return rtrim($out)."\n";
    }

    private function withSource(string $markdown, string $url, string $locale): string
    {
        return rtrim($markdown)."\n\n---\n\n".($locale === 'en' ? 'Source' : 'Bron').": {$url}\n";
    }
}
