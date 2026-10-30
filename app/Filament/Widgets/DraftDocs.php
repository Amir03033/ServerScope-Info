<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Docs\DocResource;
use App\Models\Doc;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class DraftDocs extends TableWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Concepten en geplande pagina\'s';

    public function table(Table $table): Table
    {
        return $table
            ->query(Doc::query()->where(fn ($q) => $q->where('status', 'draft')->orWhere('published_at', '>', now()))->latest('updated_at'))
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Geen concepten')
            ->recordUrl(fn (Doc $record) => DocResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('title')->label('Pagina')->description(fn (Doc $r) => strtoupper($r->locale)),
                TextColumn::make('status')->label('Status')->badge()
                    ->description(fn (Doc $r) => $r->published_at?->isFuture() ? 'gepland '.$r->published_at->format('d-m-Y H:i') : null),
                TextColumn::make('updated_at')->label('Bijgewerkt')->since(),
            ]);
    }
}
