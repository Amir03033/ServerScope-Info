<?php

namespace App\Services\Docs;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * The original documentation: content/docs/{locale}/{slug}.md with an
 * optional front matter block (order, icon, description). Used by the
 * import, the verification and as a fallback before the import ran.
 */
class MarkdownFileSource
{
    public const LOCALES = ['nl', 'en'];

    public function __construct(private readonly string $base = 'content/docs') {}

    /**
     * The new pages written for the revamp (content/docs-revamp).
     */
    public static function revamp(): self
    {
        return new self('content/docs-revamp');
    }

    public function directory(string $locale): string
    {
        return base_path("{$this->base}/{$locale}");
    }

    /**
     * @return array<int, array{locale: string, slug: string, path: string, relative_path: string, raw: string, file_sha256: string, meta: array<string, string>, body: string, body_sha256: string, title: string}>
     */
    public function all(string $locale): array
    {
        $directory = $this->directory($locale);

        if (! in_array($locale, self::LOCALES, true) || ! File::isDirectory($directory)) {
            return [];
        }

        $pages = [];

        foreach (File::files($directory) as $file) {
            $slug = $file->getFilenameWithoutExtension();

            if ($file->getExtension() !== 'md' || ! preg_match('/\A[a-zA-Z0-9_-]+\z/', $slug)) {
                continue;
            }

            $pages[] = $this->parse($locale, $slug, $file->getPathname());
        }

        usort($pages, fn (array $a, array $b): int => [(int) ($a['meta']['order'] ?? 1000), $a['title']] <=> [(int) ($b['meta']['order'] ?? 1000), $b['title']]);

        return $pages;
    }

    /**
     * Splits a file into front matter and body. The body is kept byte for
     * byte (everything after the closing ---).
     */
    public function parse(string $locale, string $slug, string $path): array
    {
        $raw = File::get($path);
        $meta = [];
        $body = $raw;

        if (preg_match('/\A---\r?\n(.*?)\r?\n---\r?\n/s', $raw, $m) === 1) {
            foreach (preg_split('/\r?\n/', $m[1]) ?: [] as $line) {
                if (preg_match('/\A([a-z_]+):\s*(.*)\z/', trim($line), $kv) === 1) {
                    $meta[$kv[1]] = trim($kv[2], " \"'");
                }
            }

            $body = substr($raw, strlen($m[0]));
        }

        $title = null;

        foreach (preg_split('/\r\n|\r|\n/', $body) ?: [] as $line) {
            if (str_starts_with(trim($line), '# ')) {
                $title = trim(substr(trim($line), 2));
                break;
            }
        }

        return [
            'locale' => $locale,
            'slug' => $slug,
            'path' => $path,
            'relative_path' => ltrim(Str::after($path, base_path()), '/\\'),
            'raw' => $raw,
            'file_sha256' => hash('sha256', $raw),
            'meta' => $meta,
            'body' => $body,
            'body_sha256' => hash('sha256', $body),
            'title' => $title ?? Str::headline($slug),
        ];
    }
}
