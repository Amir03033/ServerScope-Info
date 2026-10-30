<?php

namespace App\Filament\Resources\Legal\RelationManagers;

use App\Models\LegalDocument;
use App\Models\LegalVersion;
use App\Services\Legal\CompanyData;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VersionsRelationManager extends RelationManager
{
    protected static string $relationship = 'versions';

    protected static ?string $title = 'Versies';

    protected static ?string $modelLabel = 'versie';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Callout::make('Concept: nog niet juridisch gecontroleerd')
                ->description('Laat deze tekst nakijken door een jurist. Opmerkingen voor de jurist staan in de tekst als <!-- … --> en zijn nooit zichtbaar op de site.')
                ->warning()
                ->visible(fn (Get $get) => ! $get('legal_reviewed')),
            Callout::make('Publiceren is nog geblokkeerd')
                ->description(fn () => 'Vul eerst in onder Instellingen → Bedrijfsgegevens: '.implode(', ', $this->missing()).'.')
                ->danger()
                ->visible(fn () => $this->missing() !== []),
            Grid::make(3)->schema([
                TextInput::make('version')->label('Versie')->required()->maxLength(20)->placeholder('1.0')
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn ($rule) => $rule->where('legal_document_id', $this->getOwnerRecord()->id)),
                DatePicker::make('effective_at')->label('Ingangsdatum')->helperText('Verplicht om te publiceren.'),
                Toggle::make('legal_reviewed')->label('Juridisch gecontroleerd')->inline(false)->live(),
            ]),
            MarkdownEditor::make('body')->label('Tekst (Markdown)')->required()->minHeight('32rem')
                ->disableToolbarButtons(['attachFiles'])
                ->helperText('Hoofdstukken als "## 1. Titel", met daaronder een kader "> [!SUMMARY]" (In het kort). Plaatshouders zoals {company.name} worden ingevuld uit de bedrijfsgegevens.'),
            Textarea::make('changelog')->label('Wat is er veranderd in deze versie')->rows(3),
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function missing(): array
    {
        return app(CompanyData::class)->missingFor($this->getOwnerRecord()->key);
    }

    public function table(Table $table): Table
    {
        /** @var LegalDocument $document */
        $document = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('version')
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('version')->label('Versie')->weight('bold'),
                TextColumn::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'published' ? 'Gepubliceerd' : 'Concept')
                    ->color(fn (string $state) => $state === 'published' ? 'success' : 'gray'),
                TextColumn::make('effective_at')->label('Ingangsdatum')->date('d-m-Y')->placeholder('—'),
                IconColumn::make('legal_reviewed')->label('Juridisch gecontroleerd')->boolean(),
                TextColumn::make('updated_at')->label('Bijgewerkt')->since(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Nieuwe versie')
                    ->fillForm(fn () => [
                        'version' => '',
                        'body' => $document->versions()->first()?->body,
                        'changelog' => '',
                    ])
                    ->mutateDataUsing(fn (array $data) => $data + ['status' => 'draft']),
            ])
            ->recordActions([
                Action::make('preview')->label('Voorbeeld')->icon(Heroicon::OutlinedEye)->color('gray')
                    ->url(fn (LegalVersion $record) => route('legal.preview', $record), shouldOpenInNewTab: true),
                EditAction::make()->modalWidth('7xl')->visible(fn (LegalVersion $record) => ! $record->isPublished()),
                Action::make('publish')->label('Publiceren')->icon(Heroicon::OutlinedCheckCircle)->color('success')
                    ->visible(fn (LegalVersion $record) => ! $record->isPublished())
                    ->requiresConfirmation()
                    ->modalDescription(fn (LegalVersion $record) => $record->legal_reviewed
                        ? 'Deze versie wordt openbaar en geldt vanaf de ingangsdatum.'
                        : 'Let op: deze versie is NIET als juridisch gecontroleerd gemarkeerd. Weet je zeker dat je wilt publiceren?')
                    ->action(function (LegalVersion $record): void {
                        try {
                            $record->revisionReason = 'Gepubliceerd';
                            $record->update(['status' => 'published']);
                            Notification::make()->title('Versie '.$record->version.' gepubliceerd')->success()->send();
                        } catch (ValidationException $e) {
                            $record->refresh();
                            Notification::make()->title('Publiceren kan nog niet')->body(collect($e->errors())->flatten()->implode(' '))->danger()->persistent()->send();
                        }
                    }),
                Action::make('history')->label('Revisies')->icon(Heroicon::OutlinedClock)->color('gray')
                    ->modalHeading(fn (LegalVersion $record) => 'Revisies van versie '.$record->version)
                    ->modalSubmitAction(false)->modalCancelActionLabel('Sluiten')->modalWidth('5xl')
                    ->modalContent(fn (LegalVersion $record) => view('filament.legal-revisions', [
                        'revisions' => $record->revisions()->with('user')->get(),
                        'current' => $record->body,
                    ])),
                Action::make('restore')->label('Revisie terugzetten')->icon(Heroicon::OutlinedArrowUturnLeft)->color('warning')
                    ->visible(fn (LegalVersion $record) => ! $record->isPublished() && $record->revisions()->count() > 1)
                    ->schema(fn (LegalVersion $record) => [
                        Select::make('revision')->label('Revisie')->required()
                            ->options($record->revisions()->with('user')->get()->mapWithKeys(fn ($r) => [$r->id => $r->created_at?->format('d-m-Y H:i').' · '.($r->user?->name ?? 'Systeem').' · '.Str::limit((string) $r->reason, 40)])),
                    ])
                    ->action(function (LegalVersion $record, array $data): void {
                        $revision = $record->revisions()->findOrFail($data['revision']);
                        $record->revisionReason = 'Teruggezet naar revisie van '.$revision->created_at?->format('d-m-Y H:i');
                        $record->update(['body' => $revision->body, 'changelog' => $revision->changelog]);
                        Notification::make()->title('Revisie teruggezet')->success()->send();
                    }),
                DeleteAction::make()->visible(fn (LegalVersion $record) => ! $record->isPublished()),
            ]);
    }
}
