<?php

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PostForm
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
                ->required()
                ->maxLength(150),
            TextInput::make('slug')
                ->label('Slug (deel van de URL)')
                ->helperText('Leeg laten = automatisch uit de titel. Alleen kleine letters, cijfers en streepjes.')
                ->maxLength(150)
                ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                ->validationMessages([
                    'regex' => 'Alleen kleine letters, cijfers en enkele streepjes (niet aan het begin of eind).',
                    'unique' => 'Deze slug bestaat al voor deze taal.',
                ])
                ->unique(
                    ignoreRecord: true,
                    modifyRuleUsing: fn ($rule, $get) => $rule->where('locale', $get('locale')),
                ),
            Textarea::make('excerpt')
                ->label('Korte samenvatting')
                ->rows(3)
                ->maxLength(300)
                ->columnSpanFull(),
            MarkdownEditor::make('body')
                ->label('Artikel (Markdown)')
                ->required()
                ->columnSpanFull(),
            DateTimePicker::make('published_at')
                ->label('Publiceren op')
                ->helperText('Leeg = concept, niet zichtbaar op de site.'),
        ]);
    }
}
