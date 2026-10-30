<?php

namespace App\Console\Commands;

use App\Services\Docs\DocImporter;
use Illuminate\Console\Command;

class ImportDocs extends Command
{
    protected $signature = 'docs:import {--force : Also overwrite pages edited or deleted in the admin panel}';

    protected $description = 'Import content/docs/{nl,en}/*.md into the database (repeatable, no duplicates)';

    public function handle(DocImporter $importer): int
    {
        if ($this->option('force') && ! $this->confirm('--force overwrites changes made in the admin panel. Continue?')) {
            return self::FAILURE;
        }

        $report = $importer->run((bool) $this->option('force'));

        $this->table(['Taal', 'Slug', 'Resultaat'], array_map(fn ($r) => [$r['locale'], $r['slug'], $r['action']], $report));

        $counts = array_count_values(array_column($report, 'action'));
        $this->info(collect($counts)->map(fn ($n, $action) => "{$n} {$action}")->implode(', '));
        $this->line('Controleer nu met: php artisan docs:verify');

        return self::SUCCESS;
    }
}
