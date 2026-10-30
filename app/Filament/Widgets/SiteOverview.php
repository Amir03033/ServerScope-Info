<?php

namespace App\Filament\Widgets;

use App\Enums\FeatureStatus;
use App\Models\Doc;
use App\Models\DocFeedback;
use App\Models\Feature;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SiteOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $features = Feature::query()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');
        $feedback = DocFeedback::query()->where('created_at', '>=', now()->subDays(30));
        $total = (clone $feedback)->count();
        $helpful = (clone $feedback)->where('helpful', true)->count();

        return [
            Stat::make('Documentatie', Doc::query()->visible()->count().' online')
                ->description(Doc::query()->where('status', 'draft')->count().' concepten · '.Doc::query()->where('published_at', '>', now())->count().' gepland')
                ->icon('heroicon-o-book-open'),
            Stat::make('Functies', ($features[FeatureStatus::Available->value] ?? 0).' beschikbaar')
                ->description(($features[FeatureStatus::ComingSoon->value] ?? 0).' binnenkort · '.($features[FeatureStatus::Hidden->value] ?? 0).' verborgen')
                ->icon('heroicon-o-sparkles'),
            Stat::make('"Was dit nuttig?" (30 dagen)', $total > 0 ? round($helpful / $total * 100).'% ja' : 'Nog geen reacties')
                ->description($total.' reacties, '.($total - $helpful).' nee')
                ->icon('heroicon-o-hand-thumb-up'),
        ];
    }
}
