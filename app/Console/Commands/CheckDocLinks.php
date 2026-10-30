<?php

namespace App\Console\Commands;

use App\Models\Doc;
use App\Support\Markdown\MarkdownRenderer;
use Illuminate\Console\Command;

/**
 * Finds broken links between documentation pages: links to pages that do
 * not exist or are not visible (draft, scheduled, hidden feature) and
 * anchors (#section) that do not exist on the target page. Links into the
 * ServerScope application and to other sites are not checked.
 */
class CheckDocLinks extends Command
{
    protected $signature = 'docs:check-links';

    protected $description = 'Check links and anchors between visible documentation pages';

    public function handle(MarkdownRenderer $renderer): int
    {
        $docs = Doc::query()->visible()->with('feature')->get();
        $pages = [];

        foreach ($docs as $doc) {
            $pages[$doc->locale][$doc->slug] = $renderer->render($doc->body, $doc->locale)['html'];
        }

        $broken = [];
        $app = (string) config('serverscope.app_url');

        foreach ($docs as $doc) {
            preg_match_all('~<a\s[^>]*href="([^"]+)"~', $pages[$doc->locale][$doc->slug], $matches);

            foreach (array_unique($matches[1]) as $href) {
                $href = html_entity_decode($href);

                if (preg_match('~^(https?:|mailto:)~', $href) || str_starts_with($href, $app)) {
                    continue;
                }

                [$path, $anchor] = array_pad(explode('#', $href, 2), 2, null);
                $locale = $doc->locale;
                $slug = $doc->slug;

                if ($path !== '' && $path !== null) {
                    if (preg_match('~^/(nl|en)/docs/([a-z0-9_-]+)$~', $path, $m)) {
                        [$locale, $slug] = [$m[1], $m[2]];
                    } elseif (preg_match('~^[a-z0-9_-]+$~', $path)) {
                        $slug = $path;
                    } else {
                        continue; // other pages of this site
                    }
                }

                if (! isset($pages[$locale][$slug])) {
                    $broken[] = [$doc->locale, $doc->slug, $href, 'pagina bestaat niet of is niet zichtbaar'];
                } elseif ($anchor !== null && $anchor !== '' && ! str_contains($pages[$locale][$slug], 'id="'.$anchor.'"')) {
                    $broken[] = [$doc->locale, $doc->slug, $href, 'sectie bestaat niet'];
                }
            }
        }

        if ($broken === []) {
            $this->info("Geen kapotte links in {$docs->count()} zichtbare pagina's.");

            return self::SUCCESS;
        }

        $this->table(['Taal', 'Pagina', 'Link', 'Probleem'], $broken);
        $this->error(count($broken).' kapotte link(s).');

        return self::FAILURE;
    }
}
