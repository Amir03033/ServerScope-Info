<?php

namespace App\Filament\Concerns;

/**
 * Resources and pages for administrators only (users, settings, prices,
 * feature status). Editors manage content and documentation.
 */
trait AdminOnly
{
    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
