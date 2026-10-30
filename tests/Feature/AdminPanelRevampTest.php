<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteSettings;
use App\Filament\Resources\Docs\Pages\CreateDoc;
use App\Filament\Resources\Docs\Pages\EditDoc;
use App\Filament\Resources\Docs\Pages\ListDocs;
use App\Filament\Resources\Docs\RelationManagers\RevisionsRelationManager;
use App\Filament\Resources\Features\Pages\ManageFeatures;
use App\Filament\Resources\Plans\Pages\EditPlan;
use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Doc;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanRow;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelRevampTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
    }

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_administrators_can_open_every_page(): void
    {
        $admin = $this->user('admin');
        $doc = Doc::query()->first();
        $plan = Plan::query()->first();

        foreach (['/admin', '/admin/docs', '/admin/docs/create', "/admin/docs/{$doc->id}/edit", '/admin/doc-categories', '/admin/doc-feedback',
            '/admin/features', '/admin/plans', "/admin/plans/{$plan->id}/edit", '/admin/plan-rows', '/admin/releases', '/admin/releases/create',
            '/admin/posts', '/admin/redirects', '/admin/users', '/admin/site-settings'] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        $this->actingAs($admin)->get('/admin')
            ->assertSee('Recent gewijzigde pagina')
            ->assertSee('Concepten en geplande pagina')
            ->assertSee('Functies per status')
            ->assertSee('Was dit nuttig?');
    }

    public function test_editors_manage_content_but_not_users_settings_prices_or_feature_status(): void
    {
        $editor = $this->user('editor');

        foreach (['/admin', '/admin/docs', '/admin/doc-categories', '/admin/doc-feedback', '/admin/releases', '/admin/posts', '/admin/redirects'] as $url) {
            $this->actingAs($editor)->get($url)->assertOk();
        }

        foreach (['/admin/features', '/admin/plans', '/admin/plan-rows', '/admin/users', '/admin/site-settings'] as $url) {
            $this->actingAs($editor)->get($url)->assertForbidden();
        }

        $this->actingAs($editor)->get('/admin')->assertDontSee('Functies per status');
    }

    public function test_docs_list_searches_filters_and_bulk_publishes(): void
    {
        $this->actingAs($this->user('admin'));
        $docs = Doc::query()->where('locale', 'en')->get();

        Livewire::test(ListDocs::class)
            ->searchTable('discord-notifications')
            ->assertCanSeeTableRecords(Doc::query()->where('slug', 'discord-notifications')->get());

        Livewire::test(ListDocs::class)
            ->searchTable('zzz-bestaat-niet')
            ->assertCountTableRecords(0);

        Livewire::test(ListDocs::class)
            ->filterTable('locale', 'en')
            ->assertCountTableRecords($docs->count())
            ->callTableBulkAction('draft', $docs)
            ->assertHasNoTableBulkActionErrors();

        $this->assertSame($docs->count(), Doc::query()->where('locale', 'en')->where('status', 'draft')->count());
        $this->get('/en/docs/getting-started')->assertNotFound();

        Livewire::test(ListDocs::class)->callTableBulkAction('publish', $docs);
        $this->get('/en/docs/getting-started')->assertOk();
    }

    public function test_creating_a_page_with_preview_and_restoring_a_revision(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin);

        $component = Livewire::test(CreateDoc::class)
            ->fillForm(['title' => 'Teams', 'slug' => 'teams', 'locale' => 'nl', 'status' => 'draft', 'body' => "## Rollen\n\n```sh\nsudo bash x.sh\n```\n", 'sort' => 5, 'icon' => 'users'])
            ->callAction('preview');

        $token = collect(session()->all())->keys()->first(fn ($k) => $k === 'doc-preview') !== null
            ? array_key_first(session('doc-preview'))
            : null;
        $this->assertNotNull($token);
        $this->get(route('docs.preview', $token))->assertOk()->assertSee('Voorbeeld')->assertSee('Rollen')->assertSee('hl-keyword', false);

        $component->call('create')->assertHasNoFormErrors();

        $doc = Doc::query()->where('slug', 'teams')->sole();
        $this->assertSame($admin->id, $doc->created_by);
        $this->get('/nl/docs/teams')->assertNotFound(); // draft

        Livewire::test(EditDoc::class, ['record' => $doc->getRouteKey()])
            ->fillForm(['body' => "## Rollen\n\nNieuwe tekst.\n"])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(2, $doc->revisions()->count());
        $first = $doc->revisions()->reorder()->oldest('id')->first();

        Livewire::test(RevisionsRelationManager::class, ['ownerRecord' => $doc, 'pageClass' => EditDoc::class])
            ->assertCanSeeTableRecords($doc->revisions)
            ->callTableAction('diff', $first)
            ->callTableAction('restore', $first);

        $this->assertStringContainsString('sudo bash x.sh', $doc->fresh()->body);
        $this->assertSame(3, $doc->revisions()->count());
        $this->assertStringStartsWith('Teruggezet', $doc->revisions()->first()->reason);
    }

    public function test_preview_is_only_for_panel_users(): void
    {
        $this->get('/admin-preview/docs/'.str_repeat('a', 40))->assertForbidden();
        $this->actingAs(User::factory()->create())->get('/admin-preview/docs/'.str_repeat('a', 40))->assertForbidden();
        $this->actingAs($this->user('editor'))->get('/admin-preview/docs/'.str_repeat('a', 40))->assertNotFound();
    }

    public function test_feature_status_plans_users_and_settings_are_editable(): void
    {
        $admin = $this->user('admin');
        $this->actingAs($admin);

        $teams = Feature::query()->where('key', 'teams')->sole();
        Livewire::test(ManageFeatures::class)->callTableBulkAction('status_hidden', [$teams]);
        $this->assertSame('hidden', $teams->fresh()->status->value);

        $plan = Plan::query()->where('key', 'pro')->sole();
        $row = PlanRow::query()->where('type', 'limit')->orderBy('sort')->first();
        Livewire::test(EditPlan::class, ['record' => $plan->getRouteKey()])
            ->fillForm(['price_monthly' => '19.00', 'is_recommended' => true, "row_values.{$row->id}" => '10'])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('19.00', $plan->fresh()->price_monthly);
        $this->assertSame('10', $plan->values()->where('plan_row_id', $row->id)->value('value'));

        Livewire::test(ManageUsers::class)
            ->callAction('create', ['name' => 'Redactie', 'email' => 'red@example.com', 'role' => 'editor', 'password' => 'Lang-wachtwoord-1'])
            ->assertHasNoActionErrors();
        $this->assertSame('editor', User::query()->where('email', 'red@example.com')->value('role'));

        Livewire::test(SiteSettings::class)
            ->fillForm(['registration_open' => true, 'signup_url' => 'https://serverscope.nl/register', 'release_fingerprint' => str_repeat('ab', 32)])
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertTrue(Setting::get('registration_open'));
        $this->assertSame(str_repeat('ab', 32), Setting::get('release_fingerprint'));
    }
}
