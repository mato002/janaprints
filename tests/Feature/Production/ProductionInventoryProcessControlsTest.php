<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionJobCardStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Services\Production\JobWorkflowPresentationService;
use App\Support\Platform\SettingsRegistry;
use App\Support\Platform\SystemSettingsService;
use App\Support\Production\MaterialReadinessService;
use App\Support\Production\ProductionInventoryControlSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionInventoryProcessControlsTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_controls_setting_is_registered_for_production(): void
    {
        $settings = app(SettingsRegistry::class)->section('production')['settings'];

        $this->assertArrayHasKey('production_inventory_controls_enforced', $settings);
        $this->assertTrue($settings['production_inventory_controls_enforced']['default']);
    }

    public function test_inventory_controls_default_to_enforced(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $this->assertTrue(app(ProductionInventoryControlSettings::class)->enforced($company->id, $branch->id));
    }

    public function test_material_release_is_not_hard_gated_when_controls_are_advisory(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::Draft,
        ]);

        app(SystemSettingsService::class)->set(
            'production_inventory_controls_enforced',
            false,
            $company->id,
            $branch->id,
            'boolean',
        );

        app(MaterialReadinessService::class)->assertReadyToRelease($jobCard);

        $this->addToAssertionCount(1);
    }

    public function test_finished_goods_readiness_does_not_fail_optional_qc_and_materials(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $jobCard = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::Completed,
        ]);

        app(SystemSettingsService::class)->set(
            'production_inventory_controls_enforced',
            false,
            $company->id,
            $branch->id,
            'boolean',
        );

        $presentation = app(JobWorkflowPresentationService::class)->present($jobCard->fresh());
        $failed = collect($presentation['readiness_items'])->where('passed', false);

        $this->assertFalse($failed->contains(fn (array $item) => $item['label'] === __('QC approved')));
        $this->assertFalse($failed->contains(fn (array $item) => $item['label'] === __('Material consumption missing')));
    }
}
