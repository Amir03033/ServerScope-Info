<?php

namespace App\Console\Commands;

use App\Services\Docs\DocVerifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class VerifyDocs extends Command
{
    protected $signature = 'docs:verify';

    protected $description = 'Prove that every Markdown page is in the database with identical content (counts and SHA-256 checksums)';

    public function handle(DocVerifier $verifier): int
    {
        $result = $verifier->run();

        foreach ($result['counts'] as $locale => $count) {
            $this->line(sprintf('%s: %d bestanden, %d records %s', strtoupper($locale), $count['files'], $count['records'], $count['files'] === $count['records'] ? 'ja' : 'NEE'));
        }

        $this->table(
            ['Taal', 'Slug', 'SHA-256 body (bestand = database)', 'Velden', 'HTML', 'Zichtbaar', 'OK'],
            array_map(fn (array $p) => [
                $p['locale'],
                $p['slug'],
                substr($p['file_body_sha256'], 0, 16).'… '.($p['checks']['body_sha256'] ? '=' : '≠').' '.substr((string) $p['db_body_sha256'], 0, 16).'…',
                $p['checks']['title'] && $p['checks']['description'] && $p['checks']['order'] && $p['checks']['icon'] ? 'ja' : 'NEE',
                $p['checks']['html_sha256'] ? 'ja' : 'NEE',
                $p['checks']['visible'] ? 'ja' : 'NEE',
                $p['ok'] ? 'ja' : 'NEE: '.implode(', ', array_keys(array_filter($p['checks'], fn ($v) => ! $v))),
            ], $result['pages']),
        );

        Storage::disk('local')->put('docs-verify.json', json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->line('Rapport: '.Storage::disk('local')->path('docs-verify.json'));

        if (! $result['ok']) {
            $this->error('Niet alle pagina\'s komen overeen.');

            return self::FAILURE;
        }

        $this->info('Alle '.count($result['pages']).' pagina\'s staan in de database met exact dezelfde inhoud.');

        return self::SUCCESS;
    }
}
