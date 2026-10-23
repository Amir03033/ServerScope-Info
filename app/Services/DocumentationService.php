<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;

class DocumentationService
{
    private function directory(string $locale): string
    {
        abort_unless(in_array($locale, ['nl', 'en'], true), 404);

        return base_path("content/docs/{$locale}");
    }

    private function converter(): CommonMarkConverter
    {
        $environment = new Environment([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        $environment->addExtension(new CommonMarkCoreExtension());

        return new CommonMarkConverter([], $environment);
    }

    public function all(string $locale): array
    {
        $directory = $this->directory($locale);

        if (! File::isDirectory($directory)) {
            return [];
        }

        $articles = [];

        foreach (File::files($directory) as $file) {
            if ($file->getExtension() !== 'md') {
                continue;
            }

            $slug = $file->getFilenameWithoutExtension();
            $content = File::get($file->getPathname());

            $articles[] = [
                'slug' => $slug,
                'title' => $this->title($content, $slug),
                'excerpt' => Str::limit(
                    trim(preg_replace('/[#>*_`]/', '', $content) ?? ''),
                    180
                ),
            ];
        }

        usort(
            $articles,
            fn (array $a, array $b): int =>
            strcasecmp($a['title'], $b['title'])
        );

        return $articles;
    }

    public function find(string $locale, string $slug): ?array
    {
        if (! preg_match('/\A[a-zA-Z0-9_-]+\z/', $slug)) {
            return null;
        }

        $path = $this->directory($locale)
            . DIRECTORY_SEPARATOR
            . $slug
            . '.md';

        if (! File::exists($path)) {
            return null;
        }

        $content = File::get($path);

        return [
            'slug' => $slug,
            'title' => $this->title($content, $slug),
            'content' => $content,
            'html' => $this->converter()->convert($content)->getContent(),
        ];
    }

    private function title(string $content, string $fallback): string
    {
        foreach (preg_split('/\r\n|\r|\n/', $content) ?: [] as $line) {
            if (str_starts_with(trim($line), '# ')) {
                return trim(substr(trim($line), 2));
            }
        }

        return Str::headline($fallback);
    }
}
