<?php

namespace App\Filament\Resources\PlanRows\Pages;

use App\Filament\Resources\PlanRows\PlanRowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManagePlanRows extends ManageRecords
{
    protected static string $resource = PlanRowResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
