<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Company;
use App\Services\PrintingIntelligence\Advisor\CustomerAdvisorService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerAdvisorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_customer_advisor_returns_array(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $recs = app(CustomerAdvisorService::class)->recommend(['company_id' => $company->id]);

        $this->assertIsArray($recs);
    }
}
