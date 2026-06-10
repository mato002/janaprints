<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Models\Company;
use App\Models\PrintingIntelligence\PrintAdvisorRecommendation;
use Database\Seeders\OrganizationFoundationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdvisorCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(OrganizationFoundationSeeder::class);
        config(['printing_intelligence.advisor_enabled' => true]);
    }

    public function test_command_generates_recommendations(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->artisan('printing:advisor:generate', ['--company' => $company->id])
            ->assertSuccessful();

        $this->assertGreaterThanOrEqual(0, PrintAdvisorRecommendation::query()->where('company_id', $company->id)->count());
    }

    public function test_dry_run_does_not_persist(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $this->artisan('printing:advisor:generate', [
            '--company' => $company->id,
            '--dry-run' => true,
        ])->assertSuccessful();

        $this->assertSame(0, PrintAdvisorRecommendation::query()->where('company_id', $company->id)->count());
    }
}
