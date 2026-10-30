<?php

namespace App\Filament\Resources\PageMeta\Pages;

use App\Filament\Resources\PageMeta\PageMetaResource;
use App\Models\PageMeta;
use Filament\Resources\Pages\ManageRecords;

class ManagePageMeta extends ManageRecords
{
    protected static string $resource = PageMetaResource::class;

    protected ?string $subheading = 'Titels en beschrijvingen van de vaste pagina\'s. Documentatie, juridische pagina\'s en landingspagina\'s hebben eigen SEO-velden.';

    public function mount(): void
    {
        // One row per page and language, so every page can be edited here.
        foreach (array_keys(PageMeta::PAGES) as $page) {
            foreach (['nl', 'en'] as $locale) {
                PageMeta::query()->firstOrCreate(['page' => $page, 'locale' => $locale]);
            }
        }

        parent::mount();
    }
}
