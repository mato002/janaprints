<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Company;
use App\Services\PrintingIntelligence\Advisor\InventoryAdvisorService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryAdvisorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_inventory_advisor_returns_array(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $recs = app(InventoryAdvisorService::class)->recommend(['company_id' => $company->id]);

        $this->assertIsArray($recs);
    }
}
