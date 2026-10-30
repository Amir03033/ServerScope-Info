<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Visibility of a ServerScope feature on this site. Pages, plan rows and
 * changelog entries linked to a feature follow it:
 *
 * - Available: shown normally.
 * - ComingSoon: shown with a "coming soon" label.
 * - Hidden: not shown anywhere (navigation, search, sitemap, pricing,
 *   changelog); direct URLs return 404.
 */
enum FeatureStatus: string implements HasColor, HasLabel
{
    case Available = 'available';
    case ComingSoon = 'coming_soon';
    case Hidden = 'hidden';

    public function getLabel(): string
    {
        return match ($this) {
            self::Available => 'Beschikbaar',
            self::ComingSoon => 'Binnenkort',
            self::Hidden => 'Verborgen',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::ComingSoon => 'warning',
            self::Hidden => 'gray',
        };
    }

    public function isVisible(): bool
    {
        return $this !== self::Hidden;
    }
}
