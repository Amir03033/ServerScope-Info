<?php

namespace App\Filament\Pages;

use App\Filament\Concerns\AdminOnly;
use App\Models\Setting;
use App\Support\Crawlers;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property-read Schema $form
 */
class SiteSettings extends Page
{
    use AdminOnly;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site-instellingen';

    protected static ?string $title = 'Site-instellingen';

    protected static string|\UnitEnum|null $navigationGroup = 'Instellingen';

    protected static ?int $navigationSort = 1;

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            ...collect(Setting::DEFAULTS)->keys()->mapWithKeys(fn (string $key) => [$key => Setting::get($key)])->all(),
            'crawlers' => collect(Crawlers::LIST)->keys()->mapWithKeys(fn (string $agent) => [$agent => Crawlers::allowed($agent)])->all(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema->statePath('data')->components([
            Section::make('Aanmelden en abonnementen')
                ->description('Zolang registratie uit staat, gaan alle knoppen op de prijspagina naar de wachtlijst.')
                ->schema([
                    Toggle::make('registration_open')->label('Registratie in ServerScope staat open'),
                    TextInput::make('signup_url')->label('Registratie-URL')->url()->placeholder('https://serverscope.nl/register'),
                    TextInput::make('waitlist_url')->label('Wachtlijst-URL')->url()->helperText('Leeg = de eigen wachtlijst van deze site (aanmeldingen onder Inhoud → Wachtlijst).'),
                    Toggle::make('billing_toggle')->label('Schakelaar maandelijks/jaarlijks tonen'),
                    Grid::make(2)->schema([
                        TextInput::make('yearly_note.nl')->label('Tekst bij jaarlijks (NL)')->placeholder('bijv. 2 maanden gratis')->maxLength(60),
                        TextInput::make('yearly_note.en')->label('Tekst bij jaarlijks (EN)')->maxLength(60),
                    ]),
                ]),
            Section::make('Status en agent')->schema([
                TextInput::make('status_page_url')->label('Openbare ServerScope-statuspagina')->url()
                    ->helperText('De site haalt {url}.json aan de serverkant op (kort gecachet). Niet bereikbaar = "Status momenteel onbekend".'),
                TextInput::make('release_fingerprint')->label('Vingerafdruk releasesleutel (SHA-256)')
                    ->helperText('Verschijnt in de documentatie over agent-updates. Leeg = een duidelijke plaatshouder.')
                    ->regex('/^[0-9a-fA-F:\s]{64,95}$/')->validationMessages(['regex' => 'Vul de SHA-256 in als 64 hexadecimale tekens (met of zonder dubbele punten).']),
            ]),
            Section::make('AI-crawlers')
                ->description('Welke AI-crawlers robots.txt toelaat. Standaard staan ze allemaal aan, ook de trainingscrawlers, zodat AI-modellen ServerScope kennen. '
                    .'Een uitgezette crawler krijgt "Disallow: /". robots.txt is een verzoek: nette crawlers volgen het, het is geen afscherming. '
                    .'Zoekmachines zoals Google en Bing staan hier niet tussen en blijven altijd toegestaan. Het beheerpaneel en persoonlijke links zijn voor iedereen uitgesloten.')
                ->collapsible()
                ->schema(collect(Crawlers::KINDS)->map(fn (string $label, string $kind) => Grid::make(2)->schema(
                    collect(Crawlers::LIST)->filter(fn ($c) => $c[1] === $kind)
                        ->map(fn ($c, string $agent) => Toggle::make("crawlers.{$agent}")->label("{$agent} ({$c[0]})")->helperText($label.': '.$c[2]))
                        ->values()->all()
                ))->values()->all()),
            Section::make('Privacy')->schema([
                Toggle::make('pageview_counter')->label('Eigen bezoekersteller zonder cookies')
                    ->helperText('Telt alleen weergaven per pagina per dag. Geen IP-adressen, geen cookies, geen browsergegevens.'),
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
        foreach ($this->form->getState() as $key => $value) {
            if (array_key_exists($key, Setting::DEFAULTS)) {
                Setting::put($key, $value);
            }
        }

        Notification::make()->title('Instellingen opgeslagen')->success()->send();
    }
}
