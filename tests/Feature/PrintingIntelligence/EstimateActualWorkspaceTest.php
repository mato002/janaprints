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

class EstimateActualWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_overview_and_detail_load_with_permission(): void
    {
        [$company, $branch, $user, $comparison] = $this->fixtures();

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.estimate-vs-actual'))
            ->assertOk()
            ->assertSee(__('Estimate vs Actual'))
            ->assertSee(__('Average accuracy score'));

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.estimate-vs-actual.show', $comparison))
            ->assertOk()
            ->assertSee(__('Cost breakdown'))
            ->assertSee(__('Material'));
    }

    public function test_permissions_enforced(): void
    {
        [$company, $branch] = $this->fixtures(returnOnlyTenant: true);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions(['printing.intelligence.view']);
        $user->assignRole('Storekeeper');

        $this->actingAs($user)
            ->withSession(['active_company_id' => $company->id, 'active_branch_id' => $branch->id])
            ->get(route('admin.printing-intelligence.estimate-vs-actual'))
            ->assertForbidden();
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3?: PrintEstimateActualComparison}
     */
    protected function fixtures(bool $returnOnlyTenant = false): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        if ($returnOnlyTenant) {
            return [$company, $branch];
        }

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        Role::findByName('Storekeeper', 'web')->syncPermissions([
            'printing.estimate-actual.view',
            'printing.estimate-actual.compare',
        ]);
        $user->assignRole('Storekeeper');

        $comparison = PrintEstimateActualComparison::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'comparison_status' => EstimateActualComparisonStatus::Completed,
            'estimated_material_cost' => 100,
            'actual_material_cost' => 110,
            'material_cost_variance' => 10,
            'estimated_total_cost' => 500,
            'actual_total_cost' => 530,
            'total_cost_variance' => 30,
            'total_cost_variance_percent' => 6,
            'accuracy_score' => 94,
            'variance_class' => EstimateVarianceClass::MinorVariance,
            'recommendation' => __('Monitor ongoing variance.'),
            'compared_at' => now(),
        ]);

        return [$company, $branch, $user, $comparison];
    }
}
