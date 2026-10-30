<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('Prijspagina bekijken')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                ->url(url('/nl/pricing'), shouldOpenInNewTab: true),
        ];
    }
}
