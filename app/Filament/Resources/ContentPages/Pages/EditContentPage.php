<?php

namespace App\Filament\Resources\ContentPages\Pages;

use App\Filament\Resources\ContentPages\ContentPageResource;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class EditContentPage extends EditRecord
{
    protected static string $resource = ContentPageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view')->label('Bekijk op site')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                ->url(fn (Page $record) => url($record->path()), shouldOpenInNewTab: true)
                ->visible(fn (Page $record) => $record->status->value === 'published'),
            Action::make('preview')->label('Voorbeeld (opgeslagen versie)')->icon(Heroicon::OutlinedEye)->color('gray')
                ->url(fn (Page $record) => route('page.preview', $record), shouldOpenInNewTab: true),
            Action::make('history')->label('Revisies')->icon(Heroicon::OutlinedClock)->color('gray')
                ->modalHeading('Revisies')->modalSubmitAction(false)->modalCancelActionLabel('Sluiten')->modalWidth('5xl')
                ->modalContent(fn (Page $record) => view('filament.legal-revisions', [
                    'revisions' => $record->revisions()->with('user')->get(),
                    'current' => $record->body,
                ])),
            Action::make('restore')->label('Revisie terugzetten')->icon(Heroicon::OutlinedArrowUturnLeft)->color('warning')
                ->visible(fn (Page $record) => $record->revisions()->count() > 1)
                ->schema(fn (Page $record) => [
                    Select::make('revision')->label('Revisie')->required()
                        ->options($record->revisions()->with('user')->get()->mapWithKeys(fn ($r) => [$r->id => $r->created_at?->format('d-m-Y H:i').' · '.($r->user?->name ?? 'Systeem').' · '.Str::limit((string) $r->reason, 40)])),
                ])
                ->action(function (Page $record, array $data): void {
                    $revision = $record->revisions()->findOrFail($data['revision']);
                    $record->revisionReason = 'Teruggezet naar revisie van '.$revision->created_at?->format('d-m-Y H:i');
                    $record->update(['title' => $revision->title, 'summary' => $revision->summary, 'body' => $revision->body, 'faq' => $revision->faq]);
                    $this->refreshFormData(['title', 'summary', 'body', 'faq']);
                    Notification::make()->title('Revisie teruggezet')->success()->send();
                }),
        ];
    }
}
