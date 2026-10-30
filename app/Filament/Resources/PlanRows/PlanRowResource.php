<?php

namespace App\Filament\Resources\PlanRows;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Resources\PlanRows\Pages\ManagePlanRows;
use App\Models\Feature;
use App\Models\PlanRow;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PlanRowResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = PlanRow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $modelLabel = 'vergelijkingsregel';

    protected static ?string $pluralModelLabel = 'Vergelijkingsregels';

    protected static string|\UnitEnum|null $navigationGroup = 'Product';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)->schema([
                TextInput::make('label.nl')->label('Regel (NL)')->required()->maxLength(100),
                TextInput::make('label.en')->label('Regel (EN)')->required()->maxLength(100),
                TextInput::make('group.nl')->label('Groep (NL)')->required()->maxLength(60),
                TextInput::make('group.en')->label('Groep (EN)')->required()->maxLength(60),
                TextInput::make('hint.nl')->label('Uitleg (NL)')->maxLength(200),
                TextInput::make('hint.en')->label('Uitleg (EN)')->maxLength(200),
            ])->columnSpanFull(),
            Select::make('type')->label('Soort')->options(PlanRow::TYPES)->default('limit')->required(),
            Select::make('feature_id')->label('Functie')->helperText('Verborgen functie = regel niet tonen; binnenkort = label "binnenkort".')
                ->options(fn () => Feature::query()->orderBy('sort')->get()->mapWithKeys(fn (Feature $f) => [$f->id => $f->translate('name', 'nl')]))->searchable(),
            TextInput::make('sort')->label('Volgorde')->numeric()->default(100),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('label_nl')->label('Regel')->state(fn (PlanRow $record) => $record->translate('label', 'nl'))->searchable(['label']),
                TextColumn::make('group_nl')->label('Groep')->state(fn (PlanRow $record) => $record->translate('group', 'nl')),
                TextColumn::make('type')->label('Soort')->formatStateUsing(fn (string $state) => PlanRow::TYPES[$state] ?? $state)->badge(),
                TextColumn::make('feature.status')->label('Functie')->badge()->placeholder('—'),
            ])
            ->filters([SelectFilter::make('type')->label('Soort')->options(PlanRow::TYPES)])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManagePlanRows::route('/')];
    }
}
