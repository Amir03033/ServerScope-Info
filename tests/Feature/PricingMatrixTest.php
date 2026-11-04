<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlanRow;
use App\Models\PlanRowValue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->artisan('site:defaults')->assertSuccessful();
    }

    private function value(string $plan, string $featureKey): ?string
    {
        $row = PlanRow::query()->whereHas('feature', fn ($q) => $q->where('key', $featureKey))->sole();

        return PlanRowValue::query()->where('plan_id', Plan::query()->where('key', $plan)->value('id'))->where('plan_row_id', $row->id)->value('value');
    }

    public function test_every_cell_of_the_comparison_table_is_filled(): void
    {
        $this->assertSame(0, \App\Models\Plan::query()->count() * PlanRow::query()->count() - PlanRowValue::query()->count());
        $this->get('/nl/pricing')->assertOk()->assertDontSee('Volgt');
        $this->get('/en/pricing')->assertOk()->assertDontSee('To follow');
    }

    public function test_the_matrix_matches_what_the_application_enforces(): void
    {
        foreach (['webhooks', 'discord', 'chat-channels', 'api', 'ai-analysis'] as $gated) {
            $this->assertSame(['0', '1', '1'], [$this->value('free', $gated), $this->value('pro', $gated), $this->value('premium', $gated)], $gated);
        }
        foreach (['server-monitoring', 'ssl-monitoring', 'status-pages', 'teams', 'two-factor', 'audit-log', 'logs', 'scheduled-jobs', 'root-cause', 'nexus'] as $everywhere) {
            $this->assertSame(['1', '1', '1'], [$this->value('free', $everywhere), $this->value('pro', $everywhere), $this->value('premium', $everywhere)], $everywhere);
        }
    }

    public function test_running_it_again_never_overwrites_what_the_owner_set(): void
    {
        $row = PlanRow::query()->whereHas('feature', fn ($q) => $q->where('key', 'webhooks'))->sole();
        PlanRowValue::query()->where('plan_row_id', $row->id)->where('plan_id', Plan::query()->where('key', 'free')->value('id'))->update(['value' => '1']);

        $this->artisan('site:defaults')->assertSuccessful();

        $this->assertSame('1', $this->value('free', 'webhooks'));
    }

    public function test_cells_saved_empty_by_the_admin_panel_are_filled_too(): void
    {
        $row = PlanRow::query()->whereHas('feature', fn ($q) => $q->where('key', 'webhooks'))->sole();
        $freeId = Plan::query()->where('key', 'free')->value('id');
        PlanRowValue::query()->where('plan_row_id', $row->id)->update(['value' => '']);
        PlanRowValue::query()->where('plan_row_id', $row->id)->where('plan_id', $freeId)->update(['value' => '1']); // set by the owner

        $this->get('/nl/pricing')->assertSee('Volgt');
        $this->artisan('site:defaults')->assertSuccessful();

        $this->assertSame(['1', '1', '1'], [$this->value('free', 'webhooks'), $this->value('pro', 'webhooks'), $this->value('premium', 'webhooks')]);
        $this->get('/nl/pricing')->assertDontSee('Volgt');
    }

    public function test_features_added_later_get_a_row_in_their_group_of_an_existing_table(): void
    {
        // A table created before these features existed (as on production).
        $new = \App\Models\Feature::query()->whereIn('key', ['logs', 'ai-analysis', 'nexus'])->get();
        PlanRow::query()->whereIn('feature_id', $new->pluck('id'))->delete();
        \App\Models\Feature::query()->whereKey($new->pluck('id'))->delete();
        $removedByOwner = PlanRow::query()->whereHas('feature', fn ($q) => $q->where('key', 'maintenance'))->sole();
        $removedByOwner->delete();

        $this->artisan('site:defaults')->assertSuccessful();

        $this->assertSame(['0', '1', '1'], [$this->value('free', 'ai-analysis'), $this->value('pro', 'ai-analysis'), $this->value('premium', 'ai-analysis')]);
        $this->assertSame('1', $this->value('free', 'logs'));
        $this->assertSame('Dashboard en NEXUS', PlanRow::query()->whereHas('feature', fn ($q) => $q->where('key', 'nexus'))->sole()->translate('group', 'nl'));
        $this->assertFalse(PlanRow::query()->whereHas('feature', fn ($q) => $q->where('key', 'maintenance'))->exists());

        // The new row sits inside its group, after the rows already there.
        $groups = PlanRow::query()->orderBy('sort')->get()->map(fn (PlanRow $row) => $row->translate('group', 'nl'))->all();
        $this->assertSame(array_values(array_unique($groups)), array_values(array_unique(array_filter($groups, fn ($g, $i) => $i === 0 || $groups[$i - 1] !== $g, ARRAY_FILTER_USE_BOTH))));
        $this->get('/en/pricing')->assertOk()->assertSee('AI incident analysis')->assertSee('Dashboard and NEXUS');
    }
}
