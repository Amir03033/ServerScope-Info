<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['page', 'locale', 'title', 'description', 'noindex'])]
class PageMeta extends Model
{
    protected $table = 'page_meta';

    /** page key => [nl label, path below /{locale}, translation key of the default title, of the default description] */
    public const PAGES = [
        'home' => ['Home', '', 'site.home.title', 'site.home.description'],
        'features' => ['Functionaliteiten', '/features', 'site.features.title', 'site.features.v2_intro'],
        'docs' => ['Documentatie (overzicht)', '/docs', 'site.docs.title', 'site.docs.intro'],
        'pricing' => ['Prijzen', '/pricing', 'site.pricing.title', 'site.pricing.intro'],
        'changelog' => ['Changelog', '/changelog', 'site.changelog.title', 'site.changelog.intro'],
        'status' => ['Status', '/status', 'site.status.title', 'site.status.intro'],
        'contact' => ['Contact', '/contact', 'site.contact.title', 'site.contact.intro'],
        'waitlist' => ['Wachtlijst', '/waitlist', 'site.waitlist.title', 'site.waitlist.intro'],
        'blog' => ['Blog', '/blog', 'site.blog.title', 'site.blog.intro'],
    ];

    protected function casts(): array
    {
        return ['noindex' => 'boolean'];
    }
}
