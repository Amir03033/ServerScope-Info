<?php

namespace App\Filament\Resources\ContentPages;

use App\Enums\PublishStatus;
use App\Filament\Resources\ContentPages\Pages\EditContentPage;
use App\Filament\Resources\ContentPages\Pages\ListContentPages;
use App\Filament\Resources\PageMeta\PageMetaResource;
use App\Models\Feature;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * "Wat is ServerScope", the glossary, the comparison and the landing pages.
 * The addresses are fixed; only the content is edited here.
 */
class ContentPageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $modelLabel = 'pagina';

    protected static ?string $pluralModelLabel = 'Pagina\'s en landingspagina\'s';

    protected static ?string $navigationLabel = 'Pagina\'s';

    protected static string|\UnitEnum|null $navigationGroup = 'Inhoud';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'content-pages';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Callout::make('Alleen feiten uit de documentatie')
                ->description('Geen klantaantallen, beoordelingen, prijzen of beschikbaarheidspercentages, en geen uitspraken over andere tools. Een uitspraak over hosting in Nederland alleen als die volgt uit de bedrijfsgegevens.')
                ->warning(),
            Section::make('Inhoud')->schema([
                TextInput::make('title')->label('Titel (H1)')->required()->maxLength(200),
                TextInput::make('slug')->label('Adres')->disabled()->dehydrated(false)
                    ->prefix(fn (?Page $record) => $record ? url($record->locale).'/' : null)
                    ->helperText('Vast per pagina, zodat links blijven werken.'),
                Textarea::make('summary')->label('In het kort')->rows(3)->maxLength(600)
                    ->helperText('Twee of drie zinnen met het directe antwoord. Staat bovenaan in een kader, met de vermelding dat de volledige tekst geldt.'),
                MarkdownEditor::make('body')->label('Tekst (Markdown)')->required()->disableToolbarButtons(['attachFiles'])->minHeight('24rem')
                    ->helperText('De eerste regel "# Titel" wordt niet getoond. Link naar documentatie met /nl/docs/… of /en/docs/….'),
                Repeater::make('faq')->label('Veelgestelde vragen (zichtbaar op de pagina)')
                    ->helperText('Alleen vragen die echt op de pagina staan; ze worden ook als FAQPage-gegevens voor zoekmachines gebruikt.')
                    ->schema([
                        TextInput::make('question')->label('Vraag')->required()->maxLength(200),
                        Textarea::make('answer')->label('Antwoord (Markdown)')->required()->rows(3),
                    ])
                    ->collapsible()->itemLabel(fn (array $state) => $state['question'] ?? null)->defaultItems(0)->reorderable(),
            ]),
            Section::make('Publicatie en SEO')->columns(2)->schema([
                Select::make('status')->label('Status')->options(PublishStatus::class)->required(),
                Select::make('feature_id')->label('Functie')->helperText('Is de functie verborgen, dan verdwijnt de pagina ook.')
                    ->options(fn () => Feature::query()->orderBy('sort')->get()->mapWithKeys(fn (Feature $f) => [$f->id => $f->name['nl'] ?? $f->key]))
                    ->searchable()->nullable(),
                PageMetaResource::titleField('seo_title')->label('SEO-titel')->helperText('Leeg = de titel. " — ServerScope" komt er altijd achter.')->columnSpanFull(),
                PageMetaResource::descriptionField('seo_description')->label('Meta-omschrijving')->rows(2)->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('title')->label('Titel')->searchable()->description(fn (Page $record) => $record->path()),
                TextColumn::make('locale')->label('Taal')->badge()->formatStateUsing(fn (string $state) => strtoupper($state)),
                TextColumn::make('kind')->label('Soort')->state(fn (Page $record) => (Page::PAGES[$record->key][3] ?? '') === 'landing' ? 'Landingspagina' : 'Over ServerScope'),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('feature.name.nl')->label('Functie')->placeholder('—'),
                TextColumn::make('updated_at')->label('Gewijzigd')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('locale')->label('Taal')->options(['nl' => 'Nederlands', 'en' => 'Engels']),
                SelectFilter::make('status')->label('Status')->options(PublishStatus::class),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentPages::route('/'),
            'edit' => EditContentPage::route('/{record}/edit'),
        ];
    }
}
