<?php

namespace App\Filament\Resources\Features;

use App\Enums\FeatureStatus;
use App\Filament\Concerns\AdminOnly;
use App\Filament\Resources\Docs\Schemas\DocForm;
use App\Filament\Resources\Features\Pages\ManageFeatures;
use App\Models\Feature;
use App\Services\Site\FeatureCatalog;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class FeatureResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = Feature::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $modelLabel = 'functie';

    protected static ?string $pluralModelLabel = 'Functies';

    protected static string|\UnitEnum|null $navigationGroup = 'Product';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('key')->label('Sleutel')->helperText('Vast technisch kenmerk, bijvoorbeeld "teams".')->required()->maxLength(60)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->unique(ignoreRecord: true)->disabledOn('edit'),
            Select::make('status')->label('Status op de site')->options(FeatureStatus::class)->default(FeatureStatus::Hidden)->required()
                ->helperText('Beschikbaar = normaal tonen · Binnenkort = met label · Verborgen = nergens tonen.'),
            Grid::make(2)->schema([
                TextInput::make('name.nl')->label('Naam (NL)')->required()->maxLength(100),
                TextInput::make('name.en')->label('Naam (EN)')->required()->maxLength(100),
                Textarea::make('summary.nl')->label('Samenvatting (NL)')->rows(2)->maxLength(300),
                Textarea::make('summary.en')->label('Samenvatting (EN)')->rows(2)->maxLength(300),
            ])->columnSpanFull(),
            Select::make('category')->label('Groep')->options(collect(FeatureCatalog::CATEGORIES)->map(fn ($c) => $c[0])),
            Select::make('icon')->label('Icoon')->options(DocForm::ICONS + ['globe' => 'Wereldbol', 'alert' => 'Waarschuwing', 'link' => 'Koppeling', 'network' => 'Netwerk', 'list' => 'Lijst'])->default('pulse'),
            TextInput::make('sort')->label('Volgorde')->numeric()->default(100),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name_nl')->label('Functie')
                    ->state(fn (Feature $record) => $record->translate('name', 'nl'))
                    ->description(fn (Feature $record) => $record->translate('summary', 'nl'))
                    ->searchable(['key', 'name']),
                TextColumn::make('category')->label('Groep')->formatStateUsing(fn (?string $state) => FeatureCatalog::CATEGORIES[$state][0] ?? $state)->toggleable(),
                SelectColumn::make('status')->label('Status op de site')->options(FeatureStatus::class)->selectablePlaceholder(false),
                TextColumn::make('docs_count')->label('Pagina\'s')->counts('docs')->toggleable(),
                TextColumn::make('updated_at')->label('Bijgewerkt')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(FeatureStatus::class),
                SelectFilter::make('category')->label('Groep')->options(collect(FeatureCatalog::CATEGORIES)->map(fn ($c) => $c[0])),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([
                BulkActionGroup::make(collect(FeatureStatus::cases())->map(fn (FeatureStatus $status) => BulkAction::make('status_'.$status->value)
                    ->label('Status: '.$status->getLabel())
                    ->requiresConfirmation()
                    ->action(fn (Collection $records) => $records->each->update(['status' => $status]))
                    ->deselectRecordsAfterCompletion())->all()),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageFeatures::route('/')];
    }
}
