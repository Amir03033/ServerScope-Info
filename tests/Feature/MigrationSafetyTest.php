<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Builds the database exactly as production has it today (only the
 * migrations that exist on main), fills it with blog posts, changelog
 * entries and users, then runs every new migration of the revamp and
 * proves that no existing row or column changed.
 */
class MigrationSafetyTest extends TestCase
{
    /** Migrations present on main before the revamp. */
    private const EXISTING = [
        '0001_01_01_000000_create_users_table.php',
        '0001_01_01_000001_create_cache_table.php',
        '0001_01_01_000002_create_jobs_table.php',
        '2026_10_23_100000_create_posts_table.php',
        '2026_10_23_100100_create_releases_table.php',
        '2026_10_27_100000_add_is_admin_to_users_table.php',
    ];

    private function snapshot(string $table): array
    {
        return DB::table($table)->orderBy('id')->get()
            ->map(fn ($row) => collect((array) $row)->only($this->original[$table])->all())
            ->all();
    }

    /** @var array<string, array<int, string>> */
    private array $original = [];

    public function test_new_migrations_do_not_touch_existing_blog_posts_releases_or_users(): void
    {
        Artisan::call('migrate:fresh', ['--path' => array_map(fn ($f) => "database/migrations/{$f}", self::EXISTING), '--force' => true]);

        $this->assertFalse(Schema::hasTable('docs'));

        DB::table('posts')->insert([
            ['locale' => 'nl', 'slug' => 'eerste-bericht', 'title' => 'Eerste bericht', 'excerpt' => 'Kort', 'body' => "# Titel\n\nTekst met **Markdown** en `code`.\n", 'published_at' => '2026-10-01 10:00:00', 'created_at' => '2026-10-01 09:00:00', 'updated_at' => '2026-10-01 09:00:00'],
            ['locale' => 'en', 'slug' => 'first-post', 'title' => 'First post', 'excerpt' => null, 'body' => str_repeat("Lorem ipsum ünïcödé ✓\n", 200), 'published_at' => null, 'created_at' => '2026-10-02 09:00:00', 'updated_at' => '2026-10-02 09:00:00'],
        ]);
        DB::table('releases')->insert([
            'locale' => 'nl', 'version' => '0.1.0', 'released_at' => '2026-09-24', 'items' => json_encode([['type' => 'new', 'text' => 'Eerste versie']]),
            'created_at' => '2026-09-24 12:00:00', 'updated_at' => '2026-09-24 12:00:00',
        ]);
        DB::table('users')->insert([
            ['name' => 'Beheer', 'email' => 'admin@example.com', 'password' => 'hash', 'is_admin' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gast', 'email' => 'guest@example.com', 'password' => 'hash', 'is_admin' => false, 'created_at' => now(), 'updated_at' => now()],
        ]);

        foreach (['posts', 'releases', 'users'] as $table) {
            $this->original[$table] = DB::getSchemaBuilder()->getColumnListing($table);
        }

        $before = ['posts' => $this->snapshot('posts'), 'releases' => $this->snapshot('releases'), 'users' => $this->snapshot('users')];

        // Every migration added by the revamp.
        $new = collect(File::files(database_path('migrations')))->map->getFilename()->diff(self::EXISTING)->values();
        $this->assertNotEmpty($new);

        Artisan::call('migrate', ['--force' => true]);

        foreach ($before as $table => $rows) {
            // Existing columns still exist (additive only) and every value is unchanged.
            $this->assertEmpty(array_diff($this->original[$table], DB::getSchemaBuilder()->getColumnListing($table)), "{$table}: a column disappeared");
            $this->assertSame($rows, $this->snapshot($table), "{$table}: existing data changed");
        }

        $this->assertSame(2, DB::table('posts')->count());

        // Rolling back the revamp also leaves the old tables and their data in place.
        Artisan::call('migrate:rollback', ['--step' => $new->count(), '--force' => true]);
        $this->assertSame($before['posts'], $this->snapshot('posts'));
        $this->assertSame($before['releases'], $this->snapshot('releases'));
    }
}
