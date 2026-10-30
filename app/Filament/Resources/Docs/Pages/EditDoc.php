<?php

namespace App\Filament\Resources\Docs\Pages;

use App\Filament\Resources\Docs\DocResource;
use App\Filament\Resources\Docs\Pages\Concerns\PreviewsDoc;
use App\Models\Doc;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditDoc extends EditRecord
{
    use PreviewsDoc;

    protected static string $resource = DocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->previewAction(),
            Action::make('view')
                ->label('Bekijk op site')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (Doc $record): string => url($record->path()), shouldOpenInNewTab: true)
                ->visible(fn (Doc $record): bool => $record->isVisible()),
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make()->visible(fn (Doc $record): bool => $record->trashed() && (auth()->user()?->isAdmin() ?? false)),
        ];
    }
}
