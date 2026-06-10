<?php

namespace Tests\Feature\PrintingIntelligence;

use App\Enums\EstimateActualComparisonStatus;
use App\Enums\EstimateVarianceClass;
use App\Models\Branch;
use App\Models\Company;
use App\Models\PrintingIntelligence\PrintEstimateActualComparison;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_cross_company_comparison_show_returns_forbidden(): void
    {
        $jana = Company::query()->where('code', 'JANA')->firstOrFail();
        $janaBranch = Branch::query()->where('company_id', $jana->id)->firstOrFail();

        $otherCompany = Company::factory()->create(['code' => 'OTHER']);
        $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id]);

        $comparison = PrintEstimateActualComparison::query()->create([
            'company_id' => $otherCompany->id,
            'branch_id' => $otherBranch->id,
            'comparison_status' => EstimateActualComparisonStatus::Completed,
            'estimated_total_cost' => 500,
            'actual_total_cost' => 530,
            'total_cost_variance' => 30,
            'total_cost_variance_percent' => 6,
            'accuracy_score' => 94,
            'variance_class' => EstimateVarianceClass::MinorVariance,
            'compared_at' => now(),
        ]);

        $user = User::factory()->create([
            'company_id' => $jana->id,
            'default_branch_id' => $janaBranch->id,
            'email_verified_at' => now(),
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['printing.estimate-actual.view']);
        $user->assignRole('Storekeeper');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $jana->id, 'active_branch_id' => $janaBranch->id])
            ->get(route('admin.printing-intelligence.estimate-vs-actual.show', $comparison))
            ->assertForbidden();
    }
}
