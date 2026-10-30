<?php

namespace App\Filament\Resources\Docs\Schemas;

use App\Enums\FeatureStatus;
use App\Enums\PublishStatus;
use App\Filament\Locales;
use App\Filament\Resources\PageMeta\PageMetaResource;
use App\Models\DocCategory;
use App\Models\Feature;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DocForm
{
    public const ICONS = [
        'book' => 'Boek', 'server' => 'Server', 'globe' => 'Wereldbol', 'bell' => 'Bel', 'pulse' => 'Hartslag',
        'code' => 'Code', 'shield' => 'Schild', 'lock' => 'Slot', 'users' => 'Gebruikers', 'terminal' => 'Terminal',
        'chart' => 'Grafiek', 'chat' => 'Bericht', 'clock' => 'Klok', 'cpu' => 'Processor',
    ];

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Inhoud')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Titel')
                            ->required()
                            ->maxLength(200)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation): void {
                                if ($operation === 'create' && blank($get('slug'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),
                        Textarea::make('description')
                            ->label('Korte omschrijving')
                            ->helperText('Eén zin. Verschijnt in het overzicht, in de zoekresultaten en als meta-omschrijving.')
                            ->rows(2)
                            ->maxLength(300),
                        MarkdownEditor::make('body')
                            ->label('Tekst (Markdown)')
                            ->helperText('Codeblokken met ```sh, ```json, ```php enz. krijgen kleur. Callouts: > [!NOTE], > [!TIP], > [!WARNING], > [!IMPORTANT]. {app_url} wordt de ServerScope-URL. Een eerste regel "# Titel" wordt niet getoond: de titel komt uit het veld hierboven.')
                            ->disableToolbarButtons(['attachFiles'])
                            ->required()
                            ->minHeight('28rem'),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        Section::make('Publicatie')
                            ->schema([
                                Select::make('status')
                                    ->label('Status')
                                    ->options(PublishStatus::class)
                                    ->default(PublishStatus::Draft)
                                    ->required(),
                                DateTimePicker::make('published_at')
                                    ->label('Publiceren vanaf')
                                    ->helperText('Leeg = direct (bij status Gepubliceerd). Een datum in de toekomst plant de publicatie.')
                                    ->seconds(false),
                                Select::make('feature_id')
                                    ->label('Hoort bij functie')
                                    ->helperText('De pagina volgt de status van de functie: verborgen = niet zichtbaar, binnenkort = met label.')
                                    ->options(fn () => Feature::query()->orderBy('sort')->get()->mapWithKeys(fn (Feature $f) => [$f->id => $f->translate('name', 'nl').' ('.$f->status->getLabel().')']))
                                    ->searchable(),
                            ]),
                        Section::make('Plaats')
                            ->schema([
                                Select::make('locale')
                                    ->label('Taal')
                                    ->options(Locales::OPTIONS)
                                    ->default('nl')
                                    ->required()
                                    ->live(),
                                TextInput::make('slug')
                                    ->label('Slug (deel van de URL)')
                                    ->helperText('Wijzig je de slug, dan maakt de site automatisch een 301-redirect vanaf de oude URL.')
                                    ->required()
                                    ->maxLength(150)
                                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                                    ->validationMessages([
                                        'regex' => 'Alleen kleine letters, cijfers en enkele streepjes.',
                                        'unique' => 'Deze slug bestaat al voor deze taal.',
                                    ])
                                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule, Get $get) => $rule->where('locale', $get('locale'))),
                                TextInput::make('translation_key')
                                    ->label('Vertaalsleutel')
                                    ->helperText('Zelfde waarde in NL en EN koppelt de vertalingen. Leeg = de slug.')
                                    ->maxLength(150),
                                Select::make('doc_category_id')
                                    ->label('Categorie')
                                    ->options(fn (Get $get) => DocCategory::query()->where('locale', $get('locale') ?? 'nl')->orderBy('sort')->pluck('name', 'id')),
                                Grid::make(2)->schema([
                                    TextInput::make('sort')->label('Volgorde')->numeric()->default(100)->required(),
                                    Select::make('icon')->label('Icoon')->options(self::ICONS)->default('book')->required(),
                                ]),
                            ]),
                        Section::make('SEO')
                            ->collapsed()
                            ->schema([
                                PageMetaResource::titleField('seo_title')->label('SEO-titel')->helperText('Leeg = de titel. " — ServerScope" komt er altijd achter.'),
                                PageMetaResource::descriptionField('seo_description')->label('Meta-omschrijving')->helperText('Leeg = de korte omschrijving.')->rows(2),
                                Toggle::make('noindex')->label('Niet laten indexeren (noindex)'),
                            ]),
                    ]),
            ]);
    }

    public static function featureStatusOptions(): array
    {
        return collect(FeatureStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->getLabel()])->all();
    }
}
