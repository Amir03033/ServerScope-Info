<?php

namespace App\Filament\Resources\Docs\Pages;

use App\Filament\Resources\Docs\DocResource;
use App\Filament\Resources\Docs\Pages\Concerns\PreviewsDoc;
use Filament\Resources\Pages\CreateRecord;

class CreateDoc extends CreateRecord
{
    use PreviewsDoc;

    protected static string $resource = DocResource::class;

    protected function getHeaderActions(): array
    {
        return [$this->previewAction()];
    }
}
