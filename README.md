# ServerScope Info

The information, documentation and product website for ServerScope, intended for `info.serverscope.nl`.
The monitoring application itself (`serverscope.nl`) is a separate project; this site only links to it.

## Stack

- Laravel 13 (PHP), Blade views, no front-end framework
- Tailwind CSS 4 and Vite 8; fonts (Inter, JetBrains Mono) are bundled from npm, so the build needs no network access and pages load no third-party resources
- Filament 5 admin panel at `/admin` for blog posts and changelog releases (administrators only, see `php artisan info:admin`)

## Local setup

```sh
composer install
cp .env.example .env && php artisan key:generate
touch database/database.sqlite && php artisan migrate
npm ci && npm run build
php artisan serve
```

The committed `composer.lock` requires **PHP 8.4.1 or newer** (Symfony 8 components), although `composer.json` allows `^8.3`.

## Configuration

| Variable | Purpose |
|---|---|
| `SERVERSCOPE_APP_URL` | URL of the ServerScope application (links for log in, docs deep links, API examples). Default `https://serverscope.nl`. |
| `CONTACT_RECIPIENT` | Address that receives contact form messages, sent with the `MAIL_*` settings. Empty: the form is not shown. |
| `SERVERSCOPE_STATUS_PAGE` | Slug of a **public** status page in the ServerScope application that describes ServerScope itself. When set, `/{locale}/status` shows live data from `{app_url}/status/{slug}.json`; when empty, the page says no live status is connected. |
| `SERVERSCOPE_STATUS_CACHE` | Seconds to cache that status data (default 60). |

## Content

| What | Where |
|---|---|
| Documentation | `content/docs/{nl,en}/{slug}.md` — Markdown with front matter (`order`, `category`, `icon`, `description`). Every guide must exist in both languages with the same slug. `{app_url}` is replaced with the application URL. GitHub-style callouts (`> [!NOTE]`) are supported. Consecutive code blocks in different languages become tabs. |
| Interface text | `lang/{nl,en}/site.php` — both files must have identical keys (enforced by `tests/Feature/LocalizationFilesTest.php`). |
| Blog posts | Admin panel → Artikelen (stored in the database, per language). |
| Changelog | Admin panel → Changelog. `lang/{nl,en}/changelog.php` is only a fallback while the database has no releases for a language. |

Moved documentation pages are redirected via `DocumentationService::REDIRECTS`.

## Checks

```sh
php artisan test
npm run build
vendor/bin/pint --test
```

## Deployment

After pulling changes: `composer install --no-dev --optimize-autoloader`, `php artisan migrate --force`, `npm ci && npm run build` (or upload `public/build`), then `php artisan optimize`. `public/robots.txt` was replaced by the `/robots.txt` route, which also points to `/sitemap.xml`.
