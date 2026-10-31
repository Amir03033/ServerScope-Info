<?php

namespace App\Services\Site;

use App\Models\Doc;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanRow;
use App\Models\PlanRowValue;
use Illuminate\Support\Facades\DB;

/**
 * Creates the starting content that is missing: features (with their
 * starting status), the Free/Pro/Premium plans with empty placeholders and
 * the rows of the comparison table. Existing records are never changed.
 */
class SiteDefaults
{
    /** Imported guides that describe one feature (pages follow its status). */
    public const DOC_FEATURES = [
        'discord-notifications' => 'discord',
        'status-pages' => 'status-pages',
    ];

    /** Limit rows of the comparison table; values are filled in by the owner. */
    /**
     * Starting limits per plan, in the order of LIMIT_ROWS. They mirror
     * config('serverscope.plans') in ServerScope (the application enforces
     * those; keep both in sync). Only empty cells are filled: a value you set
     * in the admin panel is never changed.
     */
    public const LIMIT_VALUES = [
        'free' => ['1', '3', '15', '1', '∞', '5 min', '14 d'],
        'pro' => ['25', '50', '250', '5', '∞', '1 min', '365 d'],
        'premium' => ['250', '1000', '1000', '25', '∞', '1 min', '∞'],
    ];

    /**
     * Which features each plan includes (free, pro, premium), as ServerScope
     * enforces it (config 'plans' > 'features'): API tokens and webhooks (and
     * with them Discord notifications) need Pro or Premium. Everything else is
     * in every plan; the limits above differ. Keep in sync with ServerScope.
     */
    public const FEATURE_MATRIX = [
        'webhooks' => [false, true, true],
        'discord' => [false, true, true],
        'api' => [false, true, true],
    ];

    public const LIMIT_ROWS = [
        ['Servers', 'Servers'],
        ['Domeinen', 'Domains'],
        ['Pagina\'s per website', 'Pages per website'],
        ['Statuspagina\'s', 'Status pages'],
        ['Teamleden', 'Team members'],
        ['Controle-interval', 'Check interval'],
        ['Gegevens bewaren', 'Data retention'],
    ];

    /**
     * @return array<string, int> created per type
     */
    public function run(): array
    {
        $created = ['features' => 0, 'plans' => 0, 'plan_rows' => 0, 'doc_links' => 0];

        DB::transaction(function () use (&$created): void {
            $sort = 0;

            foreach (FeatureCatalog::FEATURES as $key => [$status, $category, $icon, $name, $summary]) {
                $feature = Feature::query()->firstOrCreate(['key' => $key], [
                    'status' => $status,
                    'category' => $category,
                    'icon' => $icon,
                    'name' => ['nl' => $name[0], 'en' => $name[1]],
                    'summary' => ['nl' => $summary[0], 'en' => $summary[1]],
                    'sort' => $sort += 10,
                ]);
                $created['features'] += (int) $feature->wasRecentlyCreated;
            }

            foreach ([['free', 'Free', 'Gratis starten', 'Start for free'], ['pro', 'Pro', 'Kies Pro', 'Choose Pro'], ['premium', 'Premium', 'Kies Premium', 'Choose Premium']] as $i => [$key, $name, $nl, $en]) {
                $plan = Plan::query()->firstOrCreate(['key' => $key], [
                    'name' => ['nl' => $name, 'en' => $name],
                    'cta_label' => ['nl' => $nl, 'en' => $en],
                    'sort' => ($i + 1) * 10,
                ]);
                $created['plans'] += (int) $plan->wasRecentlyCreated;
            }

            if (! PlanRow::query()->exists()) {
                $sort = 0;

                foreach (self::LIMIT_ROWS as [$nl, $en]) {
                    PlanRow::query()->create(['group' => ['nl' => 'Limieten', 'en' => 'Limits'], 'label' => ['nl' => $nl, 'en' => $en], 'type' => 'limit', 'sort' => $sort += 10]);
                    $created['plan_rows']++;
                }

                foreach (Feature::query()->orderBy('sort')->get() as $feature) {
                    PlanRow::query()->create([
                        'group' => ['nl' => FeatureCatalog::CATEGORIES[$feature->category][0] ?? 'Functies', 'en' => FeatureCatalog::CATEGORIES[$feature->category][1] ?? 'Features'],
                        'label' => $feature->name,
                        'feature_id' => $feature->id,
                        'type' => 'boolean',
                        'sort' => $sort += 10,
                    ]);
                    $created['plan_rows']++;
                }
            }

            // Fill empty cells only (limits and feature yes/no); never overwrite.
            $limitRows = PlanRow::query()->where('type', 'limit')->orderBy('sort')->get()->values();
            $featureRows = PlanRow::query()->where('type', 'boolean')->whereNotNull('feature_id')->with('feature')->get();

            foreach (['free', 'pro', 'premium'] as $index => $planKey) {
                $plan = Plan::query()->where('key', $planKey)->first();

                if ($plan === null) {
                    continue;
                }

                foreach ($limitRows as $i => $row) {
                    $value = self::LIMIT_VALUES[$planKey][$i] ?? null;

                    if ($value !== null) {
                        $this->fillIfEmpty($plan, $row, $value);
                    }
                }

                foreach ($featureRows as $row) {
                    $included = self::FEATURE_MATRIX[$row->feature->key][$index] ?? true;
                    $this->fillIfEmpty($plan, $row, $included ? '1' : '0');
                }
            }

            foreach (self::DOC_FEATURES as $slug => $key) {
                $created['doc_links'] += Doc::query()->where('slug', $slug)->whereNull('feature_id')
                    ->update(['feature_id' => Feature::query()->where('key', $key)->value('id')]);
            }
        });

        return $created;
    }

    /**
     * Fills a cell that does not exist or holds nothing. The admin panel saves
     * an empty field as an empty string, which the pricing page shows as
     * "to follow", so that counts as empty too. A value that was set is kept.
     */
    private function fillIfEmpty(Plan $plan, PlanRow $row, string $value): void
    {
        $cell = PlanRowValue::query()->where('plan_id', $plan->id)->where('plan_row_id', $row->id)->first();

        if ($cell === null) {
            PlanRowValue::query()->create(['plan_id' => $plan->id, 'plan_row_id' => $row->id, 'value' => $value]);
        } elseif (trim((string) $cell->value) === '') {
            $cell->update(['value' => $value]);
        }
    }
}
