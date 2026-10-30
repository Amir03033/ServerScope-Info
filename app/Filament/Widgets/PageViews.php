<?php

namespace App\Filament\Widgets;

use App\Models\PageView;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Most viewed pages in the last 30 days, from the cookieless counter
 * (path, day, number; nothing about visitors).
 */
class PageViews extends TableWidget
{
    protected static ?int $sort = 6;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Meest bekeken pagina\'s (30 dagen, zonder cookies)';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => PageView::query()
                ->select('path', DB::raw('sum(views) as total'), DB::raw('min(id) as id'))
                ->where('day', '>=', now()->subDays(30)->toDateString())
                ->groupBy('path')
                ->orderByDesc('total'))
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Nog geen bezoeken geteld')
            ->columns([
                TextColumn::make('path')->label('Pagina'),
                TextColumn::make('total')->label('Weergaven')->numeric(),
            ]);
    }
}
