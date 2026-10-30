<?php

namespace App\Filament\Resources\Docs\Tables;

use App\Enums\PublishStatus;
use App\Filament\Locales;
use App\Models\Doc;
use App\Models\DocCategory;
use App\Models\Feature;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DocsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Titel')
                    ->searchable(['title', 'slug', 'description', 'body'])
                    ->sortable()
                    ->description(fn (Doc $record): string => $record->path()),
                TextColumn::make('locale')
                    ->label('Taal')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => strtoupper($state)),
                TextColumn::make('category.name')
                    ->label('Categorie')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->description(fn (Doc $record): ?string => $record->published_at?->isFuture() ? 'gepland '.$record->published_at->format('d-m-Y H:i') : null),
                TextColumn::make('feature.status')
                    ->label('Functie')
                    ->badge()
                    ->placeholder('—')
                    ->tooltip(fn (Doc $record): ?string => $record->feature?->translate('name', 'nl'))
                    ->toggleable(),
                IconColumn::make('translated')
                    ->label('Vertaling')
                    ->state(fn (Doc $record): bool => Doc::query()->where('translation_key', $record->translation_key)->where('locale', '!=', $record->locale)->exists())
                    ->boolean()
                    ->tooltip(fn (bool $state): string => $state ? 'Vertaling aanwezig' : 'Vertaling ontbreekt')
                    ->toggleable(),
                TextColumn::make('sort')
                    ->label('Volgorde')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('author.name')
                    ->label('Gewijzigd door')
                    ->placeholder('Import')
                    ->toggleable(),
                TextColumn::make('updated_at')
                    ->label('Bijgewerkt')
                    ->since()
                    ->dateTimeTooltip('d-m-Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                SelectFilter::make('locale')->label('Taal')->options(Locales::OPTIONS),
                SelectFilter::make('status')->label('Status')->options(PublishStatus::class),
                SelectFilter::make('doc_category_id')
                    ->label('Categorie')
                    ->options(fn () => DocCategory::query()->orderBy('locale')->orderBy('sort')->get()->mapWithKeys(fn ($c) => [$c->id => strtoupper($c->locale).' · '.$c->name])),
                SelectFilter::make('feature_id')
                    ->label('Functie')
                    ->options(fn () => Feature::query()->orderBy('sort')->get()->mapWithKeys(fn ($f) => [$f->id => $f->translate('name', 'nl')])),
                TernaryFilter::make('scheduled')
                    ->label('Gepland')
                    ->queries(
                        true: fn (Builder $q) => $q->where('published_at', '>', now()),
                        false: fn (Builder $q) => $q->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now())),
                    ),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Bekijk')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (Doc $record): string => url($record->path()), shouldOpenInNewTab: true)
                    ->visible(fn (Doc $record): bool => $record->isVisible()),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('publish')
                        ->label('Publiceren')
                        ->icon(Heroicon::OutlinedEye)
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::setStatus($records, PublishStatus::Published))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('draft')
                        ->label('Concept maken')
                        ->icon(Heroicon::OutlinedEyeSlash)
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => self::setStatus($records, PublishStatus::Draft))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('category')
                        ->label('Categorie wijzigen')
                        ->icon(Heroicon::OutlinedFolder)
                        ->schema([
                            Select::make('doc_category_id')
                                ->label('Categorie')
                                ->options(fn () => DocCategory::query()->orderBy('locale')->orderBy('sort')->get()->mapWithKeys(fn ($c) => [$c->id => strtoupper($c->locale).' · '.$c->name]))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $category = DocCategory::query()->findOrFail($data['doc_category_id']);
                            $records->where('locale', $category->locale)->each->update(['doc_category_id' => $category->id]);
                        })
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make()->visible(fn (): bool => auth()->user()?->isAdmin() ?? false),
                ]),
            ]);
    }

    private static function setStatus(Collection $records, PublishStatus $status): void
    {
        $records->each(fn (Doc $doc) => $doc->update(['status' => $status]));

        Notification::make()->title($records->count().' pagina(\'s) bijgewerkt')->success()->send();
    }
}
