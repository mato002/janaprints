<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\PrintInkType;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Services\PrintingIntelligence\InkCostCalculator;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InkCostCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_uses_cost_per_ml_when_configured(): void
    {
        $profile = $this->profile(['cost_per_ml' => 4.5, 'estimated_ml' => 1000]);

        $result = app(InkCostCalculator::class)->calculate($profile, 10);

        $this->assertEqualsWithDelta(45.0, $result['estimated_ink_cost'], 0.01);
        $this->assertEqualsWithDelta(4.5, $result['cost_per_ml'], 0.01);
    }

    public function test_derives_cost_per_ml_from_cartridge(): void
    {
        $profile = $this->profile([
            'cartridge_cost' => 5000,
            'estimated_ml' => 1000,
            'cost_per_ml' => null,
        ]);

        $result = app(InkCostCalculator::class)->calculate($profile, 20);

        $this->assertEqualsWithDelta(100.0, $result['estimated_ink_cost'], 0.01);
        $this->assertEqualsWithDelta(5.0, $result['cost_per_ml'], 0.01);
    }

    public function test_handles_missing_yield(): void
    {
        $profile = $this->profile([
            'cartridge_cost' => 0,
            'estimated_ml' => 0,
            'cost_per_ml' => null,
        ]);

        $result = app(InkCostCalculator::class)->calculate($profile, 15);

        $this->assertNull($result['estimated_ink_cost']);
        $this->assertNotEmpty($result['warnings']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    protected function profile(array $overrides = []): PrintInkProfile
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        return PrintInkProfile::query()->create(array_merge([
            'company_id' => $company->id,
            'name' => 'Test Ink',
            'ink_type' => PrintInkType::Cmyk,
            'cartridge_cost' => 1000,
            'estimated_ml' => 500,
            'active' => true,
        ], $overrides));
    }
}
