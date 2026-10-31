<?php

namespace Tests\Feature;

use App\Filament\Resources\Waitlist\Pages\ListWaitlistEntries;
use App\Mail\ContactMessage;
use App\Models\User;
use App\Models\WaitlistEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class WaitlistAndContactTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
    }

    public function test_pricing_buttons_lead_to_the_own_waitlist(): void
    {
        $this->get('/nl/pricing')->assertSee(url('/nl/waitlist').'?plan=pro', false);
        $this->get('/nl/waitlist?plan=pro')->assertOk()
            ->assertSee('Ik geef toestemming')
            ->assertSee('<option value="pro" selected', false)
            ->assertSee('name="website"', false);
    }

    public function test_signing_up_stores_only_what_is_needed_with_consent(): void
    {
        $this->post('/nl/waitlist', ['email' => 'Klant@Example.com', 'plan' => 'pro'])->assertSessionHasErrors('consent');
        $this->post('/nl/waitlist', ['email' => 'geen-email', 'consent' => '1'])->assertSessionHasErrors('email');
        $this->post('/nl/waitlist', ['email' => 'a@example.com', 'plan' => 'bestaat-niet', 'consent' => '1'])->assertSessionHasErrors('plan');
        $this->assertSame(0, WaitlistEntry::query()->count());

        $this->freezeSecond();
        $response = $this->post('/nl/waitlist', ['email' => 'Klant@Example.com', 'plan' => 'pro', 'consent' => '1'])->assertRedirect(url('/nl/waitlist'));

        $entry = WaitlistEntry::query()->sole();
        $this->assertSame(['klant@example.com', 'nl', 'pro'], [$entry->email, $entry->locale, $entry->plan]);
        $this->assertTrue($entry->consented_at->equalTo(now()));
        $this->assertSame(trans('site.waitlist.consent', [], 'nl'), $entry->consent_text);
        $this->assertSame(['id', 'email', 'locale', 'plan', 'consent_text', 'consented_at', 'removal_token_hash', 'created_at', 'updated_at', 'notified_at'], Schema::getColumnListing('waitlist_entries'));

        $removeUrl = $response->getSession()->get('waitlist-remove-url');
        $this->assertStringStartsWith(url('/nl/waitlist/remove/'.$entry->id.'/'), $removeUrl);
        $this->get('/nl/waitlist')->assertSee('Je staat op de wachtlijst.');

        // Signing up again updates the entry instead of adding a second one.
        $this->post('/en/waitlist', ['email' => 'klant@example.com', 'plan' => 'premium', 'consent' => '1']);
        $this->assertSame(['en', 'premium'], [WaitlistEntry::query()->sole()->locale, WaitlistEntry::query()->sole()->plan]);
    }

    public function test_entry_can_be_removed_with_the_personal_link_only(): void
    {
        $removeUrl = $this->post('/nl/waitlist', ['email' => 'weg@example.com', 'consent' => '1'])->getSession()->get('waitlist-remove-url');
        $entry = WaitlistEntry::query()->sole();

        $this->get('/nl/waitlist/remove/'.$entry->id.'/'.str_repeat('a', 48))->assertOk()->assertSee('Deze link is ongeldig');
        $this->post('/nl/waitlist/remove/'.$entry->id.'/'.str_repeat('a', 48));
        $this->assertSame(1, WaitlistEntry::query()->count());

        $this->get($removeUrl)->assertOk()->assertSee('Verwijder mijn aanmelding');
        $this->post($removeUrl)->assertRedirect(url('/nl/waitlist'));
        $this->assertSame(0, WaitlistEntry::query()->count());
    }

    public function test_honeypot_and_rate_limit_stop_spam(): void
    {
        $this->post('/nl/waitlist', ['email' => 'bot@example.com', 'consent' => '1', 'website' => 'http://spam.test'])->assertRedirect(url('/nl/waitlist'));
        $this->assertSame(0, WaitlistEntry::query()->count());

        foreach (range(1, 4) as $i) {
            $this->post('/nl/waitlist', ['email' => "x{$i}@example.com", 'consent' => '1']);
        }
        $this->post('/nl/waitlist', ['email' => 'teveel@example.com', 'consent' => '1'])->assertStatus(429);
    }

    public function test_admins_see_and_export_the_waitlist_and_editors_do_not(): void
    {
        $entry = new WaitlistEntry(['email' => '=cmd@example.com', 'locale' => 'nl', 'plan' => 'pro', 'consent_text' => 'Tekst', 'consented_at' => now()]);
        $entry->issueRemovalToken();
        $entry->save();

        $editor = User::factory()->create();
        $editor->assignRole('editor');
        $this->actingAs($editor)->get('/admin/waitlist')->assertForbidden();

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->get('/admin/waitlist')->assertOk()->assertSee('=cmd@example.com');

        $csv = Livewire::test(ListWaitlistEntries::class)->callAction('exportAll')->effects['download'] ?? null;
        $this->assertNotNull($csv);
        $content = base64_decode($csv['content']);
        $this->assertStringContainsString('email,taal,abonnement,toestemming_op,toestemmingstekst', $content);
        $this->assertStringContainsString("'=cmd@example.com,nl,pro,", $content); // no formula injection
    }

    public function test_contact_form_mails_the_configured_address(): void
    {
        Mail::fake();
        config(['serverscope.contact_mail_to' => 'info@example.test']);

        $this->get('/nl/contact')->assertSee('name="website"', false)->assertDontSee('niet beschikbaar');
        $this->post('/nl/contact', ['name' => 'Jan', 'email' => 'jan@example.com', 'subject' => 'Vraag', 'message' => 'Hallo'])
            ->assertSessionHas('success');

        Mail::assertSent(ContactMessage::class, fn (ContactMessage $mail) => $mail->hasTo('info@example.test') && $mail->hasReplyTo('jan@example.com') && $mail->data['message'] === 'Hallo');

        // Honeypot: looks fine to the bot, nothing is sent.
        $this->post('/nl/contact', ['name' => 'Bot', 'email' => 'b@example.com', 'subject' => 'x', 'message' => 'x', 'website' => 'spam']);
        Mail::assertSentCount(1);
    }

    public function test_contact_form_says_so_when_no_address_is_configured(): void
    {
        Mail::fake();
        config(['serverscope.contact_mail_to' => null]);

        $this->get('/nl/contact')->assertOk()->assertSee('Het contactformulier is op dit moment niet beschikbaar')->assertDontSee('name="message"', false);
        $this->post('/nl/contact', ['name' => 'Jan', 'email' => 'jan@example.com', 'subject' => 'Vraag', 'message' => 'Hallo'])
            ->assertSessionHas('error', trans('site.contact.unavailable', [], 'nl'));
        Mail::assertNothingSent();
    }

    public function test_contact_form_reports_a_failed_delivery(): void
    {
        config(['serverscope.contact_mail_to' => 'info@example.test']);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('smtp down'));

        $this->post('/nl/contact', ['name' => 'Jan', 'email' => 'jan@example.com', 'subject' => 'Vraag', 'message' => 'Hallo'])
            ->assertSessionHas('error', trans('site.contact.send_failed', [], 'nl'));
    }
}
