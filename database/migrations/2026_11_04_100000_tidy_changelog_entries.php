<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Tidies the changelog entries that were typed into the admin panel before
 * the import existed (no source_key), and moves the dates of the imported
 * teams and one-command entries apart. Entries that do not match are left
 * alone, so this is a no-op on a fresh database.
 *
 * - v0.2.0 listed "e-mail and in-app notifications" twice: exact duplicate
 *   items are removed.
 * - v0.2.0, v0.3.0 and v0.4.0 all shared October dates with other entries:
 *   v0.2.0 and v0.3.0 get their own day.
 * - v0.4.0 (28 October) repeats the 26 October website monitoring entry
 *   item by item and its number clashes with agent 0.4.0: it becomes a
 *   draft (republish it in the admin panel to undo this).
 * - The 26 October entry said the interface is Dutch only. Unedited copies
 *   are updated by site:defaults from the Markdown file; copies edited in
 *   the panel get the same correction here.
 */
return new class extends Migration
{
    private const DATES = ['0.2.0' => '2026-10-15', '0.3.0' => '2026-10-21'];

    private const IMPORTED_DATES = ['file:draft-teams' => '2026-10-27', 'file:draft-one-command-install' => '2026-10-28'];

    private const DUTCH_ONLY = [
        "- De interface is voorlopig alleen in het Nederlands.\n" => "- De interface was bij deze release alleen in het Nederlands. Sinds 30 oktober\n  is ServerScope tweetalig: Engels standaard, Nederlands als keuze.\n",
        "- The interface is available in Dutch only for now.\n" => "- At the time of this release the interface was in Dutch only. Since 30\n  October ServerScope is bilingual: English by default, Dutch as an option.\n",
    ];

    public function up(): void
    {
        $manual = fn () => DB::table('releases')->whereNull('source_key');

        foreach ($manual()->whereNotNull('items')->get(['id', 'items']) as $release) {
            $items = json_decode((string) $release->items, true);

            if (! is_array($items)) {
                continue;
            }

            $unique = collect($items)->unique(fn ($item) => json_encode($item))->values()->all();

            if (count($unique) !== count($items)) {
                DB::table('releases')->where('id', $release->id)->update(['items' => json_encode($unique), 'updated_at' => now()]);
            }
        }

        foreach (self::DATES as $version => $date) {
            $manual()->where('version', $version)->update(['released_at' => $date]);
        }

        $manual()->where('version', '0.4.0')->whereDate('released_at', '2026-10-28')
            ->where('status', 'published')->update(['status' => 'draft', 'updated_at' => now()]);

        foreach (self::IMPORTED_DATES as $key => $date) {
            DB::table('releases')->where('source_key', $key)->update(['released_at' => $date]);
        }

        foreach (DB::table('releases')->where('source_key', 'file:2026-10-26-website-monitoring')->get(['id', 'body', 'source_sha256']) as $release) {
            $edited = hash('sha256', (string) $release->body) !== $release->source_sha256;
            $body = strtr((string) $release->body, self::DUTCH_ONLY);

            if ($edited && $body !== $release->body) {
                DB::table('releases')->where('id', $release->id)->update(['body' => $body, 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Content clean-up; nothing to roll back.
    }
};
