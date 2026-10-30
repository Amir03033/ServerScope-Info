<?php

namespace App\Filament\Resources\Docs\Pages\Concerns;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

/**
 * "Voorbeeld": shows the unsaved form state in the real site template,
 * in a new tab, for logged-in panel users only. Nothing is saved.
 */
trait PreviewsDoc
{
    protected function previewAction(): Action
    {
        return Action::make('preview')
            ->label('Voorbeeld')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->action(function (): void {
                $token = Str::random(40);
                session()->put("doc-preview.{$token}", collect($this->data)->only([
                    'title', 'description', 'body', 'locale', 'feature_id', 'slug',
                ])->all());

                $url = route('docs.preview', $token);
                $this->js('window.open('.json_encode($url).', "_blank", "noopener")');

                Notification::make()
                    ->title('Voorbeeld geopend in een nieuw tabblad')
                    ->body('Opent er niets? <a href="'.e($url).'" target="_blank" rel="noopener" class="underline">Open het voorbeeld</a>.')
                    ->success()
                    ->send();
            });
    }
}
