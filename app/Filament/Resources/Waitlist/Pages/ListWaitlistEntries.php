<?php

namespace App\Filament\Resources\Waitlist\Pages;

use App\Filament\Resources\Waitlist\WaitlistEntryResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListWaitlistEntries extends ListRecords
{
    protected static string $resource = WaitlistEntryResource::class;

    protected ?string $subheading = 'Aanmeldingen voor de wachtlijst, met het moment van toestemming. Verwijder een aanmelding als iemand daarom vraagt.';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportAll')
                ->label('Alles exporteren (CSV)')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(fn () => WaitlistEntryResource::csv(WaitlistEntryResource::exportQuery()->lazy())),
        ];
    }
}
