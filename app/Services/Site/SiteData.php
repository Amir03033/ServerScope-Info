<?php

namespace App\Services\Site;

use App\Enums\FeatureStatus;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\PlanRow;
use App\Models\Setting;
use App\Services\DocumentationService;
use Illuminate\Support\Collection;

/**
 * Data for the public pages, read from the database. Every method degrades
 * to "nothing" when the database is not available, so pages still render.
 */
class SiteData
{
    public function __construct(private readonly DocumentationService $docs) {}

    /**
     * Visible features (available and coming soon), grouped by category.
     *
     * @return Collection<string, Collection<int, Feature>>
     */
    public function featuresByCategory(): Collection
    {
        return rescue(fn () => Feature::query()->visible()->orderBy('sort')->get()->groupBy(fn (Feature $f) => $f->category ?? 'other'), collect(), report: false);
    }

    public function featureStatus(string $key): ?FeatureStatus
    {
        return rescue(fn () => Feature::query()->where('key', $key)->first()?->status, null, report: false);
    }

    /**
     * Only real counts; null when unknown (the counter is then not shown).
     *
     * @return array<string, int|null>
     */
    public function counts(string $locale): array
    {
        return [
            'features' => rescue(fn () => Feature::query()->where('status', FeatureStatus::Available->value)->count() ?: null, null, report: false),
            'docs' => rescue(fn () => count($this->docs->all($locale)) ?: null, null, report: false),
        ];
    }

    /**
     * @return array{plans: Collection<int, Plan>, rows: Collection<string, Collection<int, PlanRow>>, values: array<int, array<int, ?string>>}
     */
    public function pricing(): array
    {
        return rescue(function (): array {
            $plans = Plan::query()->where('is_visible', true)->orderBy('sort')->get();
            $rows = PlanRow::query()->with('feature')->orderBy('sort')->get()
                ->filter(fn (PlanRow $row) => $row->feature === null || $row->feature->status->isVisible());
            $values = [];

            foreach ($plans as $plan) {
                $values[$plan->id] = $plan->values()->pluck('value', 'plan_row_id')->all();
            }

            return ['plans' => $plans, 'rows' => $rows->groupBy(fn (PlanRow $row) => $row->translate('group', 'nl')), 'values' => $values];
        }, ['plans' => collect(), 'rows' => collect(), 'values' => []], report: false);
    }

    /**
     * Where a plan button goes: the plan's own link or the sign-up URL when
     * registration is open, otherwise the waiting list or the contact form.
     */
    public function planUrl(Plan $plan, string $locale): string
    {
        if (Setting::get('registration_open')) {
            return $plan->cta_url ?: (Setting::get('signup_url') ?: config('serverscope.app_url'));
        }

        return Setting::get('waitlist_url') ?: url("{$locale}/contact").'?subject='.rawurlencode(trans('site.pricing.waitlist_subject', [], $locale).' '.$plan->translate('name', $locale));
    }
}
