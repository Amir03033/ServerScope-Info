<?php

namespace App\Filament\Resources\Plans;

use App\Filament\Concerns\AdminOnly;
use App\Filament\Resources\Plans\Pages\EditPlan;
use App\Filament\Resources\Plans\Pages\ListPlans;
use App\Models\Plan;
use App\Models\PlanRow;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    use AdminOnly;

    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $modelLabel = 'abonnement';

    protected static ?string $pluralModelLabel = 'Abonnementen';

    protected static string|\UnitEnum|null $navigationGroup = 'Product';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'key';

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Section::make('Abonnement')->columnSpan(1)->schema([
                Grid::make(2)->schema([
                    TextInput::make('name.nl')->label('Naam (NL)')->required()->maxLength(40),
                    TextInput::make('name.en')->label('Naam (EN)')->required()->maxLength(40),
                    TextInput::make('tagline.nl')->label('Ondertitel (NL)')->maxLength(120),
                    TextInput::make('tagline.en')->label('Ondertitel (EN)')->maxLength(120),
                ]),
                Toggle::make('is_recommended')->label('Aanbevolen')->helperText('Krijgt het label "Aanbevolen" en valt op in de tabel.'),
                Toggle::make('is_visible')->label('Tonen op de site')->default(true),
                TextInput::make('sort')->label('Volgorde')->numeric()->default(10),
            ]),
            Section::make('Prijs')->columnSpan(1)->description('Leeg = "Prijs volgt". Bedragen zonder valutateken.')->schema([
                Grid::make(2)->schema([
                    TextInput::make('price_monthly')->label('Per maand')->numeric()->minValue(0)->step('0.01'),
                    TextInput::make('price_yearly')->label('Per jaar')->numeric()->minValue(0)->step('0.01'),
                ]),
                Select::make('currency')->label('Valuta')->options(['EUR' => 'EUR (€)', 'USD' => 'USD ($)'])->default('EUR')->required(),
                Grid::make(2)->schema([
                    TextInput::make('price_note.nl')->label('Toelichting (NL)')->placeholder('excl. btw')->maxLength(80),
                    TextInput::make('price_note.en')->label('Toelichting (EN)')->placeholder('excl. VAT')->maxLength(80),
                ]),
            ]),
            Section::make('Knop')->columnSpan(1)->description('Zolang registratie in de instellingen uit staat, gaat elke knop naar de wachtlijst of het contactformulier.')->schema([
                Grid::make(2)->schema([
                    TextInput::make('cta_label.nl')->label('Tekst (NL)')->required()->maxLength(40),
                    TextInput::make('cta_label.en')->label('Tekst (EN)')->required()->maxLength(40),
                ]),
                TextInput::make('cta_url')->label('Link als registratie open is')->helperText('Leeg = de registratie-URL uit de instellingen.')->url()->maxLength(500),
            ]),
            Section::make('Vergelijkingstabel')->columnSpan(1)
                ->description('Leeg = "volgt". Bij ja/nee-regels: ja of nee. Regels van een verborgen functie worden niet getoond.')
                ->schema(fn () => PlanRow::query()->with('feature')->orderBy('sort')->get()->map(fn (PlanRow $row) => $row->type === 'boolean'
                    ? Select::make("row_values.{$row->id}")->label($row->translate('label', 'nl'))->options(['1' => 'Ja', '0' => 'Nee'])->placeholder('Volgt')
                    : TextInput::make("row_values.{$row->id}")->label($row->translate('label', 'nl'))->placeholder('Volgt')->maxLength(200))->all()),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort')
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name_nl')->label('Abonnement')->state(fn (Plan $record) => $record->translate('name', 'nl')),
                TextColumn::make('price_monthly')->label('Per maand')->money(fn (Plan $record) => $record->currency)->placeholder('Volgt'),
                TextColumn::make('price_yearly')->label('Per jaar')->money(fn (Plan $record) => $record->currency)->placeholder('Volgt'),
                ToggleColumn::make('is_recommended')->label('Aanbevolen'),
                ToggleColumn::make('is_visible')->label('Zichtbaar'),
                IconColumn::make('complete')->label('Tabel ingevuld')->boolean()
                    ->state(fn (Plan $record) => $record->values()->whereNotNull('value')->count() >= PlanRow::query()->count()),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPlans::route('/'),
            'edit' => EditPlan::route('/{record}/edit'),
        ];
    }
}
