<?php

namespace App\Console\Commands;

use App\Services\Docs\DocImporter;
use App\Services\Docs\DraftRevisionImporter;
use App\Services\Docs\MarkdownFileSource;
use App\Services\Site\ChangelogImporter;
use App\Services\Site\SiteDefaults as Defaults;
use Illuminate\Console\Command;

/**
 * One command for a new (staging) database: imports the documentation and
 * the changelog and creates missing features and plan placeholders.
 * Repeatable; never overwrites anything changed in the admin panel.
 */
class SiteDefaults extends Command
{
    protected $signature = 'site:defaults';

    protected $description = 'Import docs and changelog, create missing features and plan placeholders (repeatable)';

    public function handle(DocImporter $docs, Defaults $defaults, ChangelogImporter $changelog): int
    {
        $this->components->info('Documentatie');
        $counts = array_count_values(array_column($docs->run(), 'action'));
        $this->line('  '.collect($counts)->map(fn ($n, $a) => "{$n} {$a}")->implode(', '));

        $this->components->info('Functies en abonnementen');
        $created = $defaults->run();
        $this->line('  '.collect($created)->map(fn ($n, $k) => "{$k}: {$n} nieuw")->implode(', '));

        // After the features exist, so new pages can be linked to them.
        $this->components->info('Nieuwe documentatie (content/docs-revamp)');
        $counts = array_count_values(array_column($docs->run(source: MarkdownFileSource::revamp()), 'action'));
        $this->line('  '.collect($counts)->map(fn ($n, $a) => "{$n} {$a}")->implode(', '));

        $this->components->info('Concept-revisies (content/docs-drafts)');
        $counts = array_count_values(array_column(app(DraftRevisionImporter::class)->run(), 'action'));
        $this->line('  '.collect($counts)->map(fn ($n, $a) => "{$n} {$a}")->implode(', '));

        $this->components->info('Changelog');
        $this->table(['Taal', 'Bron', 'Resultaat'], array_map(fn ($r) => array_values($r), $changelog->run()));

        $this->line('Controleer de documentatie met: php artisan docs:verify');

        return self::SUCCESS;
    }
}
