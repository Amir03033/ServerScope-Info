<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The top plan is called "premium" (the key ServerScope uses). Renames the
 * key of an already seeded "ultra" plan, its untouched default texts, and
 * waiting-list preferences. Texts edited in the admin are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plans') && ! DB::table('plans')->where('key', 'premium')->exists()) {
            $plan = DB::table('plans')->where('key', 'ultra')->first();

            if ($plan !== null) {
                $update = ['key' => 'premium'];

                foreach (['name' => ['Ultra', 'Premium'], 'cta_label' => ['Kies Ultra', 'Kies Premium']] as $column => [$old, $new]) {
                    $json = json_decode((string) ($plan->{$column} ?? ''), true);

                    if (is_array($json)) {
                        foreach ($json as $locale => $value) {
                            if ($value === $old || $value === str_replace('Kies', 'Choose', $old)) {
                                $json[$locale] = str_replace('Ultra', 'Premium', $value);
                            }
                        }
                        $update[$column] = json_encode($json, JSON_UNESCAPED_UNICODE);
                    }
                }

                DB::table('plans')->where('id', $plan->id)->update($update);
            }
        }

        if (Schema::hasTable('waitlist_entries')) {
            DB::table('waitlist_entries')->where('plan', 'ultra')->update(['plan' => 'premium']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('plans')) {
            DB::table('plans')->where('key', 'premium')->update(['key' => 'ultra']);
        }
        if (Schema::hasTable('waitlist_entries')) {
            DB::table('waitlist_entries')->where('plan', 'premium')->update(['plan' => 'ultra']);
        }
    }
};
