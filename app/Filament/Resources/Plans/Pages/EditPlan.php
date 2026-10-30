<?php

namespace App\Filament\Resources\Plans\Pages;

use App\Filament\Resources\Plans\PlanResource;
use App\Models\PlanRowValue;
use Filament\Resources\Pages\EditRecord;

class EditPlan extends EditRecord
{
    protected static string $resource = PlanResource::class;

    /** @var array<int|string, mixed> */
    private array $rowValues = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['row_values'] = $this->record->values()->pluck('value', 'plan_row_id')->all();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->rowValues = $data['row_values'] ?? [];
        unset($data['row_values']);

        return $data;
    }

    protected function afterSave(): void
    {
        foreach ($this->rowValues as $rowId => $value) {
            PlanRowValue::query()->updateOrCreate(
                ['plan_id' => $this->record->id, 'plan_row_id' => (int) $rowId],
                ['value' => filled($value) ? (string) $value : null],
            );
        }
    }
}
