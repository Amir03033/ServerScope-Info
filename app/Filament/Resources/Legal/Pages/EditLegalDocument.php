<?php

namespace App\Filament\Resources\Legal\Pages;

use App\Filament\Resources\Legal\LegalDocumentResource;
use App\Models\LegalDocument;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditLegalDocument extends EditRecord
{
    protected static string $resource = LegalDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('Bekijk op site')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                ->url(fn (LegalDocument $record) => url($record->path()), shouldOpenInNewTab: true)
                ->visible(fn (LegalDocument $record) => $record->currentVersion() !== null),
        ];
    }
}
