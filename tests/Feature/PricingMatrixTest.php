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
        foreach (['webhooks', 'discord', 'api'] as $gated) {
            $this->assertSame(['0', '1', '1'], [$this->value('free', $gated), $this->value('pro', $gated), $this->value('premium', $gated)], $gated);
        }
        foreach (['server-monitoring', 'ssl-monitoring', 'status-pages', 'teams', 'two-factor', 'audit-log'] as $everywhere) {
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
}
