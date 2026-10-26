<?php

namespace App\Filament\Resources\Releases\Schemas;

use Filament\Forms\Components\DatePicker;
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
            TextInput::make('version')
                ->label('Versie (zonder "v")')
                ->placeholder('0.2.0')
                ->required()
                ->maxLength(20)
                ->regex('/^\d+\.\d+\.\d+([.-][0-9A-Za-z.-]+)?$/')
                ->validationMessages(['regex' => 'Gebruik een versienummer zoals 0.2.0.'])
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn ($rule, $get) => $rule->where('locale', $get('locale')),
                ),
            DatePicker::make('released_at')
                ->label('Datum')
                ->default(now())
                ->required(),
            Repeater::make('items')
                ->label('Wijzigingen')
                ->schema([
                    Select::make('type')
                        ->label('Soort')
                        ->options(['new' => 'Toegevoegd', 'improved' => 'Verbeterd', 'changed' => 'Gewijzigd', 'fixed' => 'Opgelost'])
                        ->default('new')
                        ->required(),
                    TextInput::make('text')
                        ->label('Omschrijving')
                        ->required()
                        ->maxLength(300),
                ])
                ->minItems(1)
                ->columnSpanFull(),
        ]);
    }
}
