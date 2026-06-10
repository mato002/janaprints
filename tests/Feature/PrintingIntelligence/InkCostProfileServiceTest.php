<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\PrintInkType;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Services\PrintingIntelligence\InkCostProfileService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InkCostProfileServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_cost_per_ml_and_yields_are_read_only(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $profile = PrintInkProfile::query()->create([
            'company_id' => $company->id,
            'name' => 'EcoSolvent Black',
            'ink_type' => PrintInkType::EcoSolvent,
            'cartridge_cost' => 5000,
            'estimated_ml' => 1000,
            'estimated_yield_pages' => 5000,
            'estimated_yield_sq_m' => 120,
            'active' => true,
        ]);

        $service = app(InkCostProfileService::class);

        $this->assertEqualsWithDelta(5.0, $service->costPerMl($profile), 0.01);
        $this->assertEqualsWithDelta(1.0, $service->yieldPerPage($profile), 0.01);
        $this->assertEqualsWithDelta(41.6667, $service->yieldPerSquareMeter($profile), 0.1);
        $this->assertEqualsWithDelta(5000, $service->currentCartridgeCost($profile), 0.01);
    }
}
