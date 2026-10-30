<?php

namespace App\Filament\Resources\Redirects;

use App\Filament\Resources\Redirects\Pages\ManageRedirects;
use App\Models\Redirect;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnRight;

    protected static ?string $modelLabel = 'redirect';

    protected static ?string $pluralModelLabel = 'Redirects';

    protected static string|\UnitEnum|null $navigationGroup = 'Inhoud';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'from_path';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('from_path')->label('Van (pad)')->placeholder('/nl/docs/oude-pagina')->required()->maxLength(300)
                ->regex('~^/[^\s]*$~')->unique(ignoreRecord: true),
            TextInput::make('to_path')->label('Naar (pad of volledige URL)')->placeholder('/nl/docs/nieuwe-pagina')->required()->maxLength(300)
                ->regex('~^(/[^\s]*|https://[^\s]+)$~')->different('from_path'),
            Select::make('status_code')->label('Soort')->options([301 => '301 permanent', 302 => '302 tijdelijk'])->default(301)->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('from_path')->label('Van')->searchable()->copyable(),
                TextColumn::make('to_path')->label('Naar')->searchable(),
                TextColumn::make('status_code')->label('Code')->badge(),
                TextColumn::make('hits')->label('Gebruikt')->numeric()->sortable(),
                TextColumn::make('updated_at')->label('Bijgewerkt')->since()->sortable(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageRedirects::route('/')];
    }
}
