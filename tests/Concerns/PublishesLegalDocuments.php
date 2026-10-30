<?php

namespace Tests\Concerns;

use App\Models\LegalDocument;
use App\Models\LegalVersion;
use App\Models\Setting;
use App\Services\Legal\CompanyData;

/** Fill in dummy company details and publish legal drafts in tests. */
trait PublishesLegalDocuments
{
    protected function fillCompany(): void
    {
        foreach (array_keys(CompanyData::FIELDS) as $key) {
            if (! str_starts_with($key, 'legal.') && ! str_starts_with($key, 'retention.') && $key !== 'company.same_as' && $key !== 'company.phone') {
                Setting::put($key, match ($key) {
                    'company.vat_exempt' => false,
                    'company.audience' => 'business',
                    'security_txt.expires' => now()->addMonths(6)->toDateString(),
                    default => 'Waarde '.$key,
                });
            }
        }

        Setting::put('retention.waitlist', 'tot registratie opengaat');
        Setting::put('retention.contact', '12 maanden');
    }

    protected function publish(string $key, string $locale = 'nl'): LegalVersion
    {
        $version = LegalDocument::query()->where('key', $key)->where('locale', $locale)->sole()->versions()->sole();
        $version->update(['status' => 'published', 'effective_at' => today(), 'version' => '1.0']);

        return $version;
    }
}
