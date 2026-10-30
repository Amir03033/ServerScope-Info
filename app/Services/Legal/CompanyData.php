<?php

namespace App\Services\Legal;

use App\Models\Setting;

/**
 * Company details from the admin panel (Instellingen → Bedrijfsgegevens),
 * used as placeholders in the legal texts: {company.name}, {retention.audit_logs} …
 * Nothing is made up: an empty field stays empty, and documents that
 * need it cannot be published.
 */
class CompanyData
{
    /** Stored fields: key => [label, required for documents]. */
    public const FIELDS = [
        'company.name' => ['Bedrijfsnaam', ['privacy', 'cookies', 'imprint', 'terms', 'dpa', 'aup', 'disclosure']],
        'company.legal_form' => ['Rechtsvorm', ['imprint', 'terms', 'dpa']],
        'company.street' => ['Straat en huisnummer', ['privacy', 'imprint', 'terms', 'dpa']],
        'company.postcode' => ['Postcode', ['privacy', 'imprint', 'terms', 'dpa']],
        'company.city' => ['Plaats', ['privacy', 'imprint', 'terms', 'dpa']],
        'company.country' => ['Land', ['privacy', 'imprint', 'terms', 'dpa']],
        'company.kvk' => ['KvK-nummer', ['imprint', 'terms']],
        'company.vat' => ['Btw-nummer', []],
        'company.vat_exempt' => ['Niet btw-plichtig', []],
        'company.email' => ['Algemeen contactadres', ['imprint', 'terms']],
        'company.privacy_email' => ['Privacy-contactadres', ['privacy', 'cookies', 'dpa']],
        'company.security_email' => ['Security-contactadres', ['disclosure']],
        'company.phone' => ['Telefoonnummer', []],
        'company.host_site' => ['Hostingprovider info-site', ['privacy', 'dpa']],
        'company.host_site_country' => ['Land hosting info-site', ['privacy', 'dpa']],
        'company.host_app' => ['Hostingprovider ServerScope-app', ['privacy', 'dpa']],
        'company.host_app_country' => ['Land hosting ServerScope-app', ['privacy', 'dpa']],
        'company.mail_service' => ['E-maildienst', ['privacy', 'dpa']],
        'company.mail_service_country' => ['Land e-maildienst', ['privacy', 'dpa']],
        'company.audience' => ['Doelgroep', ['privacy', 'terms']],
        'company.same_as' => ['Links naar profielen (één per regel)', []],
        'legal.governing_law' => ['Toepasselijk recht', []],
        'legal.court' => ['Bevoegde rechter', []],
        'legal.liability_cap' => ['Maximale aansprakelijkheid', []],
        'legal.notice_period' => ['Opzegtermijn', []],
        'legal.payment_term' => ['Betalingstermijn', []],
        'retention.waitlist' => ['Bewaartermijn wachtlijst', ['privacy']],
        'retention.contact' => ['Bewaartermijn contactberichten', ['privacy']],
        'retention.accounts' => ['Bewaartermijn accounts na opzegging', []],
        'retention.metrics' => ['Bewaartermijn metingen (SERVERSCOPE_RETAIN_METRICS_DAYS)', []],
        'retention.domain_checks' => ['Bewaartermijn domeincontroles', []],
        'retention.page_checks' => ['Bewaartermijn paginacontroles', []],
        'retention.page_changes' => ['Bewaartermijn paginawijzigingen (inhoud)', []],
        'retention.notification_logs' => ['Bewaartermijn verzendlog meldingen', []],
        'retention.notifications' => ['Bewaartermijn gelezen meldingen', []],
        'retention.audit_logs' => ['Bewaartermijn auditlog', []],
        'retention.service_events' => ['Bewaartermijn service-events', []],
        'retention.predictions' => ['Bewaartermijn voorspellingen', []],
        'security_txt.expires' => ['Verloopdatum security.txt', ['disclosure']],
    ];

    public const AUDIENCES = ['business' => 'Zakelijk', 'consumer' => 'Consumenten', 'both' => 'Zakelijk en consumenten'];

    public function get(string $key): mixed
    {
        return Setting::get($key);
    }

    public function filled(string $key): bool
    {
        $value = $this->get($key);

        return is_bool($value) ? true : filled($value);
    }

    /**
     * Labels of the required fields that are still empty for a document.
     *
     * @return array<int, string>
     */
    public function missingFor(string $documentKey): array
    {
        $missing = [];

        foreach (self::FIELDS as $key => [$label, $documents]) {
            if (in_array($documentKey, $documents, true) && ! $this->filled($key)) {
                $missing[] = $label;
            }
        }

        // A VAT number, or the box "not VAT liable", for the imprint.
        if ($documentKey === 'imprint' && blank($this->get('company.vat')) && ! $this->get('company.vat_exempt')) {
            $missing[] = 'Btw-nummer (of vink "Niet btw-plichtig" aan)';
        }

        return $missing;
    }

    /**
     * Placeholder => value for one language. Values are escaped for Markdown.
     *
     * @return array<string, string>
     */
    public function placeholders(string $locale): array
    {
        $nl = $locale !== 'en';
        $v = fn (string $key): string => $this->escape((string) ($this->get($key) ?? ''));
        $none = $nl ? 'geen automatische termijn ingesteld; de gegevens blijven bewaard tot ze worden verwijderd' : 'no automatic period set; the data is kept until it is deleted';

        $values = [];

        foreach (array_keys(self::FIELDS) as $key) {
            $values['{'.$key.'}'] = $v($key);
        }

        $address = trim($v('company.street').', '.trim($v('company.postcode').' '.$v('company.city')).', '.$v('company.country'), ', ');
        $values['{company.address}'] = $address;
        $values['{company.vat}'] = filled($this->get('company.vat')) ? $v('company.vat') : ($this->get('company.vat_exempt') ? ($nl ? 'niet btw-plichtig' : 'not VAT registered') : '');
        $values['{company.audience}'] = match ($this->get('company.audience')) {
            'business' => $nl ? 'zakelijke klanten' : 'business customers',
            'consumer' => $nl ? 'consumenten' : 'consumers',
            'both' => $nl ? 'zakelijke klanten en consumenten' : 'business customers and consumers',
            default => '',
        };
        // Facts from the configuration of this site.
        $values['{cookie.session}'] = (string) config('session.cookie');
        $values['{cookie.lifetime}'] = (string) config('session.lifetime');

        $values['{security_txt.expires}'] = filled($this->get('security_txt.expires')) ? (string) $this->get('security_txt.expires') : '';

        foreach (array_keys(self::FIELDS) as $key) {
            if (str_starts_with($key, 'retention.') && blank($this->get($key))) {
                $values['{'.$key.'}'] = $none;
            }
        }

        foreach (['legal.governing_law', 'legal.court', 'legal.liability_cap', 'legal.notice_period', 'legal.payment_term'] as $key) {
            if (blank($this->get($key))) {
                $values['{'.$key.'}'] = $nl ? '[nog in te vullen]' : '[to be filled in]';
            }
        }

        return $values;
    }

    public function fill(string $markdown, string $locale): string
    {
        return strtr($markdown, $this->placeholders($locale));
    }

    private function escape(string $value): string
    {
        return addcslashes(strip_tags($value), '\\*_[]<>`#|');
    }
}
