<?php

namespace App\Filament\Resources\Docs\RelationManagers;

use App\Models\Doc;
use App\Models\DocRevision;
use App\Support\TextDiff;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RevisionsRelationManager extends RelationManager
{
    protected static string $relationship = 'revisions';

    protected static ?string $title = 'Revisiegeschiedenis';

    protected static ?string $modelLabel = 'revisie';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('reason')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Wanneer')->dateTime('d-m-Y H:i')->description(fn (DocRevision $r) => $r->created_at?->diffForHumans()),
                TextColumn::make('user.name')->label('Wie')->placeholder('Systeem'),
                TextColumn::make('reason')->label('Wat')->searchable()->wrap()
                    ->badge(fn (DocRevision $r) => $r->is_draft)
                    ->color(fn (DocRevision $r) => $r->isPendingDraft() ? 'warning' : ($r->is_draft ? 'gray' : null))
                    ->description(fn (DocRevision $r) => $r->isPendingDraft() ? 'Concept, niet gepubliceerd' : ($r->is_draft ? 'Concept, gepubliceerd op '.$r->applied_at?->format('d-m-Y H:i') : null)),
                TextColumn::make('title')->label('Titel')->limit(50),
                TextColumn::make('meta.status')->label('Status')->badge()->placeholder('—'),
            ])
            ->recordActions([
                Action::make('diff')
                    ->label('Verschil')
                    ->icon(Heroicon::OutlinedArrowsRightLeft)
                    ->modalHeading('Verschil met de huidige versie')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Sluiten')
                    ->modalWidth('5xl')
                    ->modalContent(fn (DocRevision $record) => view('filament.revision-diff', [
                        'revision' => $record,
                        'title' => [$record->title, $this->getOwnerRecord()->title],
                        'description' => [$record->description, $this->getOwnerRecord()->description],
                        'lines' => TextDiff::compact($record->body, $this->getOwnerRecord()->body),
                    ])),
                Action::make('publishDraft')
                    ->label('Concept publiceren')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('success')
                    ->visible(fn (DocRevision $record) => $record->isPendingDraft())
                    ->requiresConfirmation()
                    ->modalDescription('De tekst van dit concept wordt de gepubliceerde tekst van deze pagina. De huidige versie blijft in de geschiedenis staan en kan worden teruggezet.')
                    ->action(function (DocRevision $record): void {
                        /** @var Doc $doc */
                        $doc = $this->getOwnerRecord();
                        $doc->publishDraft($record);

                        Notification::make()->title('Concept gepubliceerd')->success()->send();
                        $this->redirect(request()->header('Referer') ?? url()->current());
                    }),
                Action::make('restore')
                    ->visible(fn (DocRevision $record) => ! $record->is_draft)
                    ->label('Terugzetten')
                    ->icon(Heroicon::OutlinedArrowUturnLeft)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalDescription('Titel, omschrijving en tekst worden teruggezet naar deze revisie. De huidige versie blijft in de geschiedenis staan.')
                    ->action(function (DocRevision $record): void {
                        /** @var Doc $doc */
                        $doc = $this->getOwnerRecord();
                        $doc->revisionReason = 'Teruggezet naar revisie van '.$record->created_at?->format('d-m-Y H:i');
                        $doc->update(['title' => $record->title, 'description' => $record->description, 'body' => $record->body]);

                        Notification::make()->title('Revisie teruggezet')->success()->send();
                        $this->redirect(request()->header('Referer') ?? url()->current());
                    }),
            ]);
    }
}
