<?php

namespace App\Filament\Resources\ContentPages\Pages;

use App\Filament\Resources\ContentPages\ContentPageResource;
use Filament\Resources\Pages\ListRecords;

class ListContentPages extends ListRecords
{
    protected static string $resource = ContentPageResource::class;

    public function getSubheading(): ?string
    {
        return 'De pagina\'s komen uit content/pages (php artisan site:defaults) en worden daarna hier beheerd. Elke opslag maakt een revisie.';
    }
}
