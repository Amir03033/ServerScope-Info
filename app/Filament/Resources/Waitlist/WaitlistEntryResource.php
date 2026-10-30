<?php

namespace App\Filament\Resources\Waitlist;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Locales;
use App\Filament\Resources\Waitlist\Pages\ListWaitlistEntries;
use App\Models\Plan;
use App\Models\WaitlistEntry;
use BackedEnum;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Waiting list sign-ups (personal data: administrators only).
 */
class WaitlistEntryResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = WaitlistEntry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxStack;

    protected static ?string $modelLabel = 'aanmelding';

    protected static ?string $pluralModelLabel = 'Wachtlijst';

    protected static ?string $navigationLabel = 'Wachtlijst';

    protected static string|\UnitEnum|null $navigationGroup = 'Inhoud';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'email';

    protected static ?string $slug = 'waitlist';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = WaitlistEntry::query()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('email')->label('E-mailadres')->copyable(),
            TextEntry::make('locale')->label('Taal')->formatStateUsing(fn (string $state) => Locales::OPTIONS[$state] ?? $state),
            TextEntry::make('plan')->label('Abonnement')->placeholder('Geen voorkeur'),
            TextEntry::make('consented_at')->label('Toestemming gegeven op')->dateTime('d-m-Y H:i:s'),
            TextEntry::make('consent_text')->label('Getoonde toestemmingstekst')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('consented_at', 'desc')
            ->columns([
                TextColumn::make('email')->label('E-mailadres')->searchable()->copyable(),
                TextColumn::make('locale')->label('Taal')->badge()->formatStateUsing(fn (string $state) => strtoupper($state)),
                TextColumn::make('plan')->label('Abonnement')->placeholder('Geen voorkeur')->sortable(),
                TextColumn::make('consented_at')->label('Toestemming')->dateTime('d-m-Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('locale')->label('Taal')->options(Locales::OPTIONS),
                SelectFilter::make('plan')->label('Abonnement')->options(fn () => Plan::query()->orderBy('sort')->pluck('key', 'key')),
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make()->modalDescription('Verwijder de aanmelding, bijvoorbeeld op verzoek van de persoon zelf. Dit kan niet ongedaan worden gemaakt.'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('export')
                        ->label('Exporteren (CSV)')
                        ->icon(Heroicon::OutlinedArrowDownTray)
                        ->action(fn (Collection $records) => self::csv($records->sortBy('consented_at')))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * CSV download (UTF-8 with BOM for Excel). Cells that start with
     * =, +, - or @ are prefixed so spreadsheets do not run them as formulas.
     *
     * @param  iterable<WaitlistEntry>  $entries
     */
    public static function csv(iterable $entries): StreamedResponse
    {
        $safe = fn (?string $value): string => preg_match('/^[=+\-@\t\r]/', (string) $value) ? "'".$value : (string) $value;

        return response()->streamDownload(function () use ($entries, $safe): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['email', 'taal', 'abonnement', 'toestemming_op', 'toestemmingstekst'], escape: '');

            foreach ($entries as $entry) {
                fputcsv($out, [$safe($entry->email), $entry->locale, $safe($entry->plan), $entry->consented_at->toIso8601String(), $safe($entry->consent_text)], escape: '');
            }

            fclose($out);
        }, 'wachtlijst-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * All entries, oldest first, for the "export all" header action.
     */
    public static function exportQuery(): Builder
    {
        return WaitlistEntry::query()->orderBy('consented_at');
    }

    public static function getPages(): array
    {
        return ['index' => ListWaitlistEntries::route('/')];
    }
}
