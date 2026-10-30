<?php

namespace Tests\Feature;

use App\Filament\Pages\CompanySettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\Legal\CompanyData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CompanyDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_nothing_is_prefilled_and_missing_fields_are_listed_per_document(): void
    {
        $company = app(CompanyData::class);

        $this->assertContains('Bedrijfsnaam', $company->missingFor('imprint'));
        $this->assertContains('KvK-nummer', $company->missingFor('imprint'));
        $this->assertContains('Btw-nummer (of vink "Niet btw-plichtig" aan)', $company->missingFor('imprint'));
        $this->assertContains('Security-contactadres', $company->missingFor('disclosure'));
        $this->assertNotContains('KvK-nummer', $company->missingFor('privacy'));

        Setting::put('company.vat_exempt', true);
        $this->assertNotContains('Btw-nummer (of vink "Niet btw-plichtig" aan)', $company->missingFor('imprint'));
    }

    public function test_placeholders_are_filled_escaped_and_never_invented(): void
    {
        $company = app(CompanyData::class);
        Setting::put('company.name', 'Voorbeeld_Bedrijf');
        Setting::put('company.street', 'Straat 1');
        Setting::put('company.postcode', '1234 AB');
        Setting::put('company.city', 'Plaats');
        Setting::put('company.country', 'Nederland');
        Setting::put('retention.audit_logs', '12 maanden');

        $this->assertSame('Voorbeeld\\_Bedrijf, Straat 1, 1234 AB Plaats, Nederland', $company->fill('{company.name}, {company.address}', 'nl'));
        $this->assertSame('12 maanden', $company->fill('{retention.audit_logs}', 'nl'));
        $this->assertStringContainsString('geen automatische termijn', $company->fill('{retention.metrics}', 'nl'));
        $this->assertSame('[to be filled in]', $company->fill('{legal.governing_law}', 'en'));
    }

    public function test_admins_edit_company_details_and_editors_cannot(): void
    {
        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $this->actingAs($editor)->get('/admin/company-settings')->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get('/admin/company-settings')->assertOk()->assertSee('Ontbreekt nog');

        Livewire::test(CompanySettings::class)
            ->fillForm(['company__name' => 'Test BV', 'company__privacy_email' => 'privacy@example.test'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Test BV', Setting::get('company.name'));
        $this->assertSame('privacy@example.test', Setting::get('company.privacy_email'));
    }
}
