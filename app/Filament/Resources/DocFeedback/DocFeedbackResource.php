<?php

namespace App\Filament\Resources\DocFeedback;

use App\Filament\Locales;
use App\Filament\Resources\DocFeedback\Pages\ListDocFeedback;
use App\Models\DocFeedback;
use BackedEnum;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DocFeedbackResource extends Resource
{
    protected static ?string $model = DocFeedback::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandThumbUp;

    protected static ?string $modelLabel = 'feedback';

    protected static ?string $pluralModelLabel = 'Feedback "Was dit nuttig?"';

    protected static ?string $navigationLabel = 'Feedback';

    protected static string|\UnitEnum|null $navigationGroup = 'Documentatie';

    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Wanneer')->dateTime('d-m-Y H:i')->sortable(),
                TextColumn::make('doc.title')->label('Pagina')->searchable()->url(fn (DocFeedback $r) => $r->doc ? url($r->doc->path()) : null, shouldOpenInNewTab: true),
                TextColumn::make('doc.locale')->label('Taal')->badge()->formatStateUsing(fn (?string $state) => strtoupper((string) $state)),
                IconColumn::make('helpful')->label('Nuttig')->boolean(),
                TextColumn::make('comment')->label('Opmerking')->wrap()->searchable()->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('helpful')->label('Nuttig'),
                SelectFilter::make('locale')->label('Taal')->options(Locales::OPTIONS)
                    ->query(fn (Builder $q, array $data) => filled($data['value']) ? $q->whereHas('doc', fn ($d) => $d->where('locale', $data['value'])) : $q),
                SelectFilter::make('doc_id')->label('Pagina')->relationship('doc', 'title')->searchable(),
            ])
            ->toolbarActions([DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListDocFeedback::route('/')];
    }
}
