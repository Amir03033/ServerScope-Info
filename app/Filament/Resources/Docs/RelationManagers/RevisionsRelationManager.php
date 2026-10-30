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
                TextColumn::make('reason')->label('Wat')->searchable(),
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
                Action::make('restore')
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
