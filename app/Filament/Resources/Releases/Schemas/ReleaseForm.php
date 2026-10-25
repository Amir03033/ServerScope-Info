<?php

namespace App\Filament\Resources\Releases\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('locale')
                    ->required(),
                TextInput::make('version')
                    ->required(),
                DatePicker::make('released_at')
                    ->required(),
                Textarea::make('items')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }
}
