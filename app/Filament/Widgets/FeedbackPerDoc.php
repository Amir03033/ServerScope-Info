<?php

namespace App\Filament\Widgets;

use App\Models\Doc;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class FeedbackPerDoc extends TableWidget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Feedback per pagina';

    public function table(Table $table): Table
    {
        return $table
            ->query(Doc::query()
                ->whereHas('feedback')
                ->withCount(['feedback', 'feedback as helpful_count' => fn (Builder $q) => $q->where('helpful', true)])
                ->orderByDesc('feedback_count'))
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Nog geen feedback')
            ->columns([
                TextColumn::make('title')->label('Pagina')->description(fn (Doc $r) => strtoupper($r->locale)),
                TextColumn::make('feedback_count')->label('Reacties')->numeric(),
                TextColumn::make('helpful_count')->label('Nuttig')
                    ->formatStateUsing(fn (Doc $r) => round($r->helpful_count / max(1, $r->feedback_count) * 100).'%'),
            ]);
    }
}
