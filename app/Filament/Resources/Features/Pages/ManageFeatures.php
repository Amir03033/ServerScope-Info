<?php

namespace App\Filament\Resources\Features\Pages;

use App\Filament\Resources\Features\FeatureResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageFeatures extends ManageRecords
{
    protected static string $resource = FeatureResource::class;

    protected ?string $subheading = 'Bepaal per functie wat de site toont. Pagina\'s, prijsregels en changelog-items die bij een functie horen, volgen deze status.';

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Alle'),
            'available' => Tab::make('Beschikbaar')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'available')),
            'coming_soon' => Tab::make('Binnenkort')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'coming_soon')),
            'hidden' => Tab::make('Verborgen')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'hidden')),
        ];
    }
}
