<?php

namespace App\Filament\Resources\Releases\Schemas;

use App\Enums\PublishStatus;
use App\Models\Feature;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('locale')
                ->label('Taal')
                ->options(['nl' => 'Nederlands', 'en' => 'English'])
                ->default('nl')
                ->required(),
            TextInput::make('title')
                ->label('Titel')
                ->helperText('Bijvoorbeeld "Teams en rollen". Leeg = de versie als kop.')
                ->maxLength(200)
                ->requiredWithout('version'),
            TextInput::make('version')
                ->label('Versie (zonder "v", optioneel)')
                ->placeholder('0.2.0')
                ->dehydrateStateUsing(fn (?string $state): string => (string) $state)
                ->maxLength(20)
                ->regex('/^\d+\.\d+\.\d+([.-][0-9A-Za-z.-]+)?$/')
                ->validationMessages(['regex' => 'Gebruik een versienummer zoals 0.2.0.'])
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn ($rule, $get) => $rule->where('locale', $get('locale'))->where('version', '!=', ''),
                ),
            DatePicker::make('released_at')
                ->label('Datum')
                ->default(now())
                ->required(),
            Select::make('status')
                ->label('Status')
                ->options(PublishStatus::class)
                ->default(PublishStatus::Published)
                ->required(),
            Select::make('feature_id')
                ->label('Hoort bij functie')
                ->helperText('Verborgen functie = item niet tonen.')
                ->options(fn () => Feature::query()->orderBy('sort')->get()->mapWithKeys(fn (Feature $f) => [$f->id => $f->translate('name', 'nl')]))
                ->searchable(),
            MarkdownEditor::make('body')
                ->label('Tekst (Markdown, optioneel)')
                ->disableToolbarButtons(['attachFiles'])
                ->columnSpanFull(),
            Repeater::make('items')
                ->label('Korte wijzigingen (optioneel)')
                ->defaultItems(0)
                ->schema([
                    Select::make('type')
                        ->label('Soort')
                        ->options(['new' => 'Nieuw', 'improved' => 'Verbeterd', 'fixed' => 'Opgelost'])
                        ->default('new')
                        ->required(),
                    TextInput::make('text')
                        ->label('Omschrijving')
                        ->required()
                        ->maxLength(300),
                ])
                ->columnSpanFull(),
        ]);
    }
}
