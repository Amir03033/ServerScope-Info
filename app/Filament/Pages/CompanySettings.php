<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AdminOnly;
use App\Models\Setting;
use App\Services\Legal\CompanyData;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * Company details used by the legal texts. Nothing is prefilled: empty
 * required fields block publishing the documents that need them.
 *
 * @property-read Schema $form
 */
class CompanySettings extends Page
{
    use AdminOnly;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Bedrijfsgegevens';

    protected static ?string $title = 'Bedrijfsgegevens';

    protected static string|\UnitEnum|null $navigationGroup = 'Instellingen';

    protected static ?int $navigationSort = 2;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(collect(CompanyData::FIELDS)->keys()->mapWithKeys(fn (string $key) => [str_replace('.', '__', $key) => Setting::get($key)])->all());
    }

    private function field(string $key): string
    {
        return str_replace('.', '__', $key);
    }

    private function label(string $key): string
    {
        $label = CompanyData::FIELDS[$key][0];
        $documents = CompanyData::FIELDS[$key][1];

        return $documents === [] ? $label.' (optioneel)' : $label;
    }

    public function form(Schema $schema): Schema
    {
        $text = fn (string $key) => TextInput::make($this->field($key))->label($this->label($key))->maxLength(200);
        $retention = fn (string $key) => TextInput::make($this->field($key))->label($this->label($key))->placeholder('bijv. 12 maanden')->maxLength(100);

        return $schema->statePath('data')->components([
            Text::make(fn () => $this->missingSummary())->color('warning'),
            Section::make('Bedrijf')->columns(2)->schema([
                $text('company.name'),
                $text('company.legal_form')->placeholder('bijv. eenmanszaak of bv'),
                $text('company.street'),
                $text('company.postcode'),
                $text('company.city'),
                $text('company.country'),
                $text('company.kvk'),
                $text('company.vat'),
                Toggle::make($this->field('company.vat_exempt'))->label('Niet btw-plichtig'),
                Select::make($this->field('company.audience'))->label($this->label('company.audience'))->options(CompanyData::AUDIENCES),
            ]),
            Section::make('Contact')->columns(2)->schema([
                $text('company.email')->email(),
                $text('company.privacy_email')->email(),
                $text('company.security_email')->email()->helperText('Voor security.txt en responsible disclosure. Mag hetzelfde zijn als het algemene adres.'),
                $text('company.phone'),
                Textarea::make($this->field('company.same_as'))->label($this->label('company.same_as'))->helperText('Alleen echte profielen, bijv. LinkedIn of GitHub. Worden gebruikt in de gestructureerde data (sameAs).')->rows(3)->columnSpanFull(),
            ]),
            Section::make('Hosting en e-mail')->columns(2)->schema([
                $text('company.host_site'), $text('company.host_site_country'),
                $text('company.host_app'), $text('company.host_app_country'),
                $text('company.mail_service'), $text('company.mail_service_country'),
            ]),
            Section::make('Voorwaarden (optioneel; laat dit door een jurist bepalen)')->columns(2)->collapsed()->schema([
                $text('legal.governing_law'), $text('legal.court'), $text('legal.liability_cap'), $text('legal.notice_period'), $text('legal.payment_term'),
            ]),
            Section::make('Bewaartermijnen')->description('Leeg = de tekst zegt dat er geen automatische termijn is ingesteld. Noem alleen termijnen die echt zo zijn ingesteld (SERVERSCOPE_RETAIN_* in de app).')->columns(2)->schema(
                collect(CompanyData::FIELDS)->keys()->filter(fn ($k) => str_starts_with($k, 'retention.'))->map(fn ($k) => $retention($k))->values()->all()
            ),
            Section::make('security.txt')->schema([
                DatePicker::make($this->field('security_txt.expires'))->label($this->label('security_txt.expires'))
                    ->helperText('Maximaal een jaar vooruit (RFC 9116). Zonder datum of contactadres is security.txt niet beschikbaar.')
                    ->minDate(now())->maxDate(now()->addYear()),
            ]),
        ]);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([Actions::make([Action::make('save')->label('Opslaan')->submit('save')])]),
        ]);
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $field => $value) {
            $key = str_replace('__', '.', $field);

            if (array_key_exists($key, CompanyData::FIELDS)) {
                Setting::put($key, is_string($value) ? trim($value) : $value);
            }
        }

        Notification::make()->title('Bedrijfsgegevens opgeslagen')->success()->send();
    }

    private function missingSummary(): string
    {
        $company = app(CompanyData::class);
        $lines = [];

        foreach (['privacy' => 'Privacyverklaring', 'cookies' => 'Cookieverklaring', 'imprint' => 'Colofon', 'terms' => 'Voorwaarden', 'dpa' => 'Verwerkersovereenkomst', 'aup' => 'Acceptable use', 'disclosure' => 'Responsible disclosure'] as $doc => $label) {
            $missing = $company->missingFor($doc);

            if ($missing !== []) {
                $lines[] = $label.': '.implode(', ', $missing);
            }
        }

        return $lines === [] ? 'Alle verplichte velden zijn ingevuld.' : 'Ontbreekt nog (publiceren is geblokkeerd): '.implode(' · ', $lines);
    }
}
