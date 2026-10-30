<?php

namespace App\Filament\Resources\Legal;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Locales;
use App\Filament\Resources\Legal\Pages\EditLegalDocument;
use App\Filament\Resources\Legal\Pages\ListLegalDocuments;
use App\Filament\Resources\Legal\RelationManagers\VersionsRelationManager;
use App\Filament\Resources\PageMeta\PageMetaResource;
use App\Models\LegalDocument;
use App\Services\Legal\CompanyData;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * Legal documents (privacy, cookies, imprint, terms, DPA, AUP, disclosure),
 * each with versions. Administrators only: these are binding texts.
 */
class LegalDocumentResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = LegalDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $modelLabel = 'juridisch document';

    protected static ?string $pluralModelLabel = 'Juridische documenten';

    protected static ?string $navigationLabel = 'Juridisch';

    protected static string|\UnitEnum|null $navigationGroup = 'Inhoud';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'legal';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->label('Titel')->required()->maxLength(150),
            TextInput::make('slug')->label('Adres')->prefix(fn (LegalDocument $record) => '/'.$record->locale.'/')->disabled()->helperText('Vast per taal.'),
            PageMetaResource::descriptionField('seo_description')->label('Meta-omschrijving')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('title')->label('Document')->searchable()->description(fn (LegalDocument $r) => $r->path()),
                TextColumn::make('locale')->label('Taal')->badge()->formatStateUsing(fn (string $state) => strtoupper($state)),
                TextColumn::make('current')->label('Geldende versie')
                    ->state(fn (LegalDocument $r) => ($v = $r->currentVersion()) ? $v->version.' (sinds '.$v->effective_at->format('d-m-Y').')' : 'Niet gepubliceerd')
                    ->badge()->color(fn (LegalDocument $r) => $r->currentVersion() ? 'success' : 'gray'),
                TextColumn::make('drafts')->label('Concepten')
                    ->state(fn (LegalDocument $r) => $r->versions()->where('status', 'draft')->count()),
                TextColumn::make('missing')->label('Bedrijfsgegevens')
                    ->state(fn (LegalDocument $r) => count(app(CompanyData::class)->missingFor($r->key)) ?: 'compleet')
                    ->formatStateUsing(fn ($state) => is_int($state) ? $state.' ontbreken' : $state)
                    ->badge()->color(fn ($state) => is_int($state) ? 'warning' : 'success'),
            ])
            ->filters([SelectFilter::make('locale')->label('Taal')->options(Locales::OPTIONS)])
            ->recordActions([EditAction::make()]);
    }

    public static function getRelations(): array
    {
        return [VersionsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegalDocuments::route('/'),
            'edit' => EditLegalDocument::route('/{record}/edit'),
        ];
    }
}
