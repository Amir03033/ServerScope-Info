<?php

namespace App\Filament\Resources\Releases\Tables;

use App\Enums\PublishStatus;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReleasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->state(fn ($record): string => $record->title ?: 'v'.$record->version)
                    ->description(fn ($record): ?string => filled($record->version) ? 'v'.$record->version : null)
                    ->searchable(['title', 'version', 'body']),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),
                TextColumn::make('locale')
                    ->label('Taal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('released_at')
                    ->label('Datum')
                    ->date('d-m-Y')
                    ->sortable(),
                TextColumn::make('items')
                    ->label('Wijzigingen')
                    ->state(fn ($record): int => count($record->items ?? [])),
                TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('released_at', 'desc')
            ->filters([
                SelectFilter::make('locale')
                    ->label('Taal')
                    ->options(['nl' => 'Nederlands', 'en' => 'English']),
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PublishStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publiceren')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => PublishStatus::Published]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('draft')
                        ->label('Concept maken')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => PublishStatus::Draft]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
