<?php

namespace App\Filament\Widgets;

use App\Enums\FeatureStatus;
use App\Filament\Resources\Features\FeatureResource;
use App\Models\Feature;
use Filament\Actions\Action;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class FeaturesByStatus extends TableWidget
{
    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Functies per status';

    public static function canView(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Feature::query()->orderByRaw("case status when 'coming_soon' then 0 when 'hidden' then 1 else 2 end")->orderBy('sort'))
            ->defaultPaginationPageOption(10)
            ->headerActions([
                Action::make('all')->label('Alle functies')->url(FeatureResource::getUrl())->link(),
            ])
            ->columns([
                TextColumn::make('name_nl')->label('Functie')->state(fn (Feature $r) => $r->translate('name', 'nl')),
                SelectColumn::make('status')->label('Status')->options(FeatureStatus::class)->selectablePlaceholder(false),
            ]);
    }
}
