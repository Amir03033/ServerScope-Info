<?php

namespace App\Http\Controllers;

use App\Support\Markdown\MarkdownRenderer;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\File;

/**
 * Terms and privacy statement, from content/legal/{locale}/{page}.md.
 * Company details come from the configuration; while they are incomplete
 * the page says so and is not indexed.
 */
class LegalController extends Controller
{
    public const PAGES = ['terms', 'privacy'];

    public function show(MarkdownRenderer $markdown, string $locale, string $page): View
    {
        abort_unless(in_array($page, self::PAGES, true), 404);

        $path = base_path("content/legal/{$locale}/{$page}.md");
        abort_unless(File::exists($path), 404);

        $company = (array) config('serverscope.company');
        $missing = $locale === 'en' ? '[to be completed]' : '[nog in te vullen]';
        $value = fn (string $key) => filled($company[$key] ?? null) ? e((string) $company[$key]) : $missing;
        $updated = Carbon::parse((string) config('serverscope.legal_updated'))->locale($locale)->isoFormat('D MMMM YYYY');

        $body = str_replace(
            ['{company_name}', '{company_kvk}', '{company_vat}', '{company_address}', '{company_email}', '{hosting_provider}', '{legal_date}'],
            [$value('name'), $value('kvk'), $value('vat_id'), $value('address'), $value('email'), $value('hosting_provider'), $updated],
            File::get($path),
        );

        $incomplete = collect(['name', 'kvk', 'address', 'email'])->contains(fn ($key) => blank($company[$key] ?? null));

        return view('pages.legal', [
            'locale' => $locale,
            'page' => $page,
            'html' => $markdown->render($body, $locale)['html'],
            'updated' => $updated,
            'incomplete' => $incomplete,
        ]);
    }
}
