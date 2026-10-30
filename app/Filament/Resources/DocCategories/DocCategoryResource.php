<?php

namespace App\Filament\Resources\DocCategories;

use App\Filament\Locales;
use App\Filament\Resources\DocCategories\Pages\ManageDocCategories;
use App\Filament\Resources\Docs\Schemas\DocForm;
use App\Models\DocCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocCategoryResource extends Resource
{
    protected static ?string $model = DocCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?string $modelLabel = 'categorie';

    protected static ?string $pluralModelLabel = 'Categorieën';

    protected static string|\UnitEnum|null $navigationGroup = 'Documentatie';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('locale')->label('Taal')->options(Locales::OPTIONS)->default('nl')->required()->live(),
            TextInput::make('name')->label('Naam')->required()->maxLength(100),
            TextInput::make('slug')->label('Slug')->required()->maxLength(100)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, Get $get) => $rule->where('locale', $get('locale'))),
            TextInput::make('translation_key')->label('Vertaalsleutel')->helperText('Zelfde waarde in NL en EN.')->required()->maxLength(100)
                ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, Get $get) => $rule->where('locale', $get('locale'))),
            Textarea::make('description')->label('Omschrijving')->rows(2)->columnSpanFull(),
            Select::make('icon')->label('Icoon')->options(DocForm::ICONS)->default('book')->required(),
            TextInput::make('sort')->label('Volgorde')->numeric()->default(100)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')->label('Naam')->searchable(),
                TextColumn::make('locale')->label('Taal')->badge()->formatStateUsing(fn (string $state) => strtoupper($state)),
                TextColumn::make('translation_key')->label('Sleutel')->toggleable(),
                TextColumn::make('docs_count')->label('Pagina\'s')->counts('docs'),
                TextColumn::make('sort')->label('Volgorde')->sortable(),
            ])
            ->filters([SelectFilter::make('locale')->label('Taal')->options(Locales::OPTIONS)])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageDocCategories::route('/')];
    }
}
