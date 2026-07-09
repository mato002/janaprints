<?php

namespace Tests\Feature\Production;

use App\Models\Branch;
use App\Models\Company;
use App\Models\User;
use App\Services\Production\ProductionOperationsIntelligenceService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionOperationsIntelligenceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_live_executive_metrics_returns_expected_keys(): void
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $metrics = app(ProductionOperationsIntelligenceService::class)
            ->liveExecutiveMetrics($company->id, $branch->id);

        foreach ([
            'jobs_running',
            'jobs_waiting',
            'jobs_overdue',
            'production_backlog',
            'top_customers',
            'most_delayed_jobs',
        ] as $key) {
            $this->assertArrayHasKey($key, $metrics, "Missing key: {$key}");
        }
    }
}
