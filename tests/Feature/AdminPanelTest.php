<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Releases\Pages\CreateRelease;
use App\Models\Post;
use App\Models\Release;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        return $user;
    }

    public function test_guests_are_sent_to_the_panel_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/posts')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertSee('ServerScope Info');
    }

    public function test_regular_users_cannot_use_the_panel(): void
    {
        $user = User::factory()->create(['email' => 'someone@example.com']);

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/posts')->assertForbidden();
        $this->actingAs($user)->get('/admin/releases/create')->assertForbidden();
    }

    public function test_admin_flag_cannot_be_mass_assigned(): void
    {
        $user = User::create(['name' => 'X', 'email' => 'x@example.com', 'password' => 'Secret-password-1', 'is_admin' => true]);

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_administrators_can_open_every_resource_page(): void
    {
        $admin = $this->admin();
        $post = Post::create(['locale' => 'nl', 'title' => 'Eerste', 'body' => 'Tekst']);
        $release = Release::create(['locale' => 'nl', 'version' => '0.1.0', 'released_at' => '2026-09-24', 'items' => [['type' => 'new', 'text' => 'Start']]]);

        foreach (['/admin', '/admin/posts', '/admin/posts/create', "/admin/posts/{$post->id}/edit", '/admin/releases', '/admin/releases/create', "/admin/releases/{$release->id}/edit"] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }

        // Dutch labels in the panel.
        $this->actingAs($admin)->get('/admin/posts/create')->assertSee('Artikel (Markdown)')->assertSee('Publiceren op');
        $this->actingAs($admin)->get('/admin/releases')->assertSee('Changelog');
    }

    public function test_post_slugs_are_validated_and_unique_per_locale(): void
    {
        $this->actingAs($this->admin());
        Post::create(['locale' => 'nl', 'slug' => 'nieuws', 'title' => 'Nieuws', 'body' => 'x']);

        Livewire::test(CreatePost::class)
            ->fillForm(['locale' => 'nl', 'title' => 'Ander nieuws', 'slug' => 'nieuws', 'body' => 'x'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'unique']);

        Livewire::test(CreatePost::class)
            ->fillForm(['locale' => 'nl', 'title' => 'Fout', 'slug' => 'Hoofd Letters!', 'body' => 'x'])
            ->call('create')
            ->assertHasFormErrors(['slug' => 'regex']);

        // Same slug in another language is fine.
        Livewire::test(CreatePost::class)
            ->fillForm(['locale' => 'en', 'title' => 'News', 'slug' => 'nieuws', 'body' => 'x'])
            ->call('create')
            ->assertHasNoFormErrors();

        // An empty slug is derived from the title and kept unique.
        Livewire::test(CreatePost::class)
            ->fillForm(['locale' => 'nl', 'title' => 'Nieuws', 'slug' => '', 'body' => 'x'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(['nieuws', 'nieuws-2'], Post::query()->where('locale', 'nl')->orderBy('id')->pluck('slug')->all());
    }

    public function test_editing_a_post_keeps_its_own_slug(): void
    {
        $this->actingAs($this->admin());
        $post = Post::create(['locale' => 'nl', 'slug' => 'blijft', 'title' => 'Titel', 'body' => 'x']);

        Livewire::test(EditPost::class, ['record' => $post->getRouteKey()])
            ->fillForm(['title' => 'Nieuwe titel'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['blijft', 'Nieuwe titel'], [$post->fresh()->slug, $post->fresh()->title]);
    }

    public function test_releases_are_created_with_items_and_unique_versions(): void
    {
        $this->actingAs($this->admin());

        Livewire::test(CreateRelease::class)
            ->fillForm([
                'locale' => 'nl',
                'version' => '0.2.0',
                'released_at' => '2026-10-27',
                'items' => [['type' => 'new', 'text' => 'Discord-meldingen']],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $release = Release::query()->sole();
        $this->assertSame([['type' => 'new', 'text' => 'Discord-meldingen']], array_values($release->items));

        Livewire::test(CreateRelease::class)
            ->fillForm(['locale' => 'nl', 'version' => '0.2.0', 'released_at' => '2026-10-28', 'items' => [['type' => 'fixed', 'text' => 'x']]])
            ->call('create')
            ->assertHasFormErrors(['version' => 'unique']);

        $this->withoutVite();
        $this->get('/nl/changelog')->assertOk()->assertSee('Discord-meldingen');
    }

    public function test_admin_command_creates_promotes_and_revokes(): void
    {
        $this->artisan('info:admin', ['email' => 'Admin@Example.com'])
            ->expectsQuestion('Name', 'Beheer')
            ->expectsQuestion('Password (min. 12 characters, mixed case and a number)', 'Correct-horse-42')
            ->expectsQuestion('Repeat password', 'Correct-horse-42')
            ->assertSuccessful();

        $admin = User::query()->where('email', 'admin@example.com')->sole();
        $this->assertTrue($admin->is_admin);
        $this->assertTrue(Hash::check('Correct-horse-42', $admin->password));

        $this->artisan('info:admin', ['email' => 'weak@example.com'])
            ->expectsQuestion('Name', 'Zwak')
            ->expectsQuestion('Password (min. 12 characters, mixed case and a number)', 'password')
            ->assertFailed();
        $this->assertDatabaseMissing('users', ['email' => 'weak@example.com']);

        $user = User::factory()->create(['email' => 'editor@example.com']);
        $this->artisan('info:admin', ['email' => 'editor@example.com'])->assertSuccessful();
        $this->assertTrue($user->fresh()->is_admin);

        $this->artisan('info:admin', ['email' => 'editor@example.com', '--revoke' => true])->assertSuccessful();
        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_public_site_is_unaffected_by_the_panel(): void
    {
        $this->withoutVite();

        $this->get('/')->assertRedirect('/nl');
        $this->get('/nl')->assertOk();
        $this->get('/en/docs')->assertOk();
    }
}
