<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\ProfitabilitySnapshotType;
use App\Models\PrintingIntelligence\PrintProfitabilitySnapshot;
use App\Services\PrintingIntelligence\ProductProfitabilityService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductProfitabilityServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_ranks_products_by_margin(): void
    {
        $company = \App\Models\Company::query()->where('code', 'JANA')->firstOrFail();

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Product,
            'revenue' => 8000,
            'total_cost' => 4000,
            'gross_profit' => 4000,
            'gross_margin_percent' => 50,
            'snapshot_date' => now()->toDateString(),
            'metadata' => ['product_key' => 'banners', 'product_label' => 'Banners', 'job_count' => 3],
        ]);

        PrintProfitabilitySnapshot::query()->create([
            'company_id' => $company->id,
            'snapshot_type' => ProfitabilitySnapshotType::Product,
            'revenue' => 2000,
            'total_cost' => 1900,
            'gross_profit' => 100,
            'gross_margin_percent' => 5,
            'snapshot_date' => now()->toDateString(),
            'metadata' => ['product_key' => 'flyers', 'product_label' => 'Flyers', 'job_count' => 1],
        ]);

        $result = app(ProductProfitabilityService::class)->analyze(['company_id' => $company->id]);

        $this->assertSame('Banners', $result['highest_margin']['product_label']);
        $this->assertSame('Flyers', $result['lowest_margin']['product_label']);
    }
}
