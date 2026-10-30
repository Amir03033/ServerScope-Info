<?php

namespace App\Filament\Resources\Docs\Pages;

use App\Filament\Resources\Docs\DocResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListDocs extends ListRecords
{
    protected static string $resource = DocResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Alle'),
            'nl' => Tab::make('Nederlands')->modifyQueryUsing(fn (Builder $q) => $q->where('locale', 'nl')),
            'en' => Tab::make('English')->modifyQueryUsing(fn (Builder $q) => $q->where('locale', 'en')),
            'drafts' => Tab::make('Concepten')->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'draft')),
        ];
    }
}
