<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Docs\DocResource;
use App\Models\Doc;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentDocs extends TableWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Recent gewijzigde pagina\'s';

    public function table(Table $table): Table
    {
        return $table
            ->query(Doc::query()->with('author')->latest('updated_at')->limit(8))
            ->paginated(false)
            ->recordUrl(fn (Doc $record) => DocResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('title')->label('Pagina')->description(fn (Doc $r) => strtoupper($r->locale).' · '.$r->path()),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('author.name')->label('Door')->placeholder('Import'),
                TextColumn::make('updated_at')->label('Wanneer')->since(),
            ]);
    }
}
