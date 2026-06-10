<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Company;
use App\Services\PrintingIntelligence\Advisor\MachineAdvisorService;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MachineAdvisorServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_machine_advisor_returns_recommendations_array(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $recs = app(MachineAdvisorService::class)->recommend(['company_id' => $company->id]);

        $this->assertIsArray($recs);
    }
}
