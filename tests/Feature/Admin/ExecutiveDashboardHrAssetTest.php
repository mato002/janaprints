<?php

namespace Tests\Feature\Admin;

use App\Enums\EmploymentStatus;
use App\Enums\FixedAssetStatus;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobTitle;
use App\Models\User;
use App\Support\Dashboard\ExecutiveAssetIntelligenceService;
use App\Support\Dashboard\ExecutiveDashboardPresenter;
use App\Support\Dashboard\ExecutiveHrIntelligenceService;
use App\Support\TenantContext;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ExecutiveDashboardHrAssetTest extends TestCase
{
    use RefreshDatabase;

    protected Company $company;

    protected Branch $branch;

    protected User $ceo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);

        $this->company = Company::query()->where('code', 'JANA')->firstOrFail();
        $this->branch = Branch::query()->where('company_id', $this->company->id)->where('code', 'HQ')->firstOrFail();
        $this->ceo = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $this->ceo->assignRole('Company Admin');

        app()->instance(TenantContext::class, new TenantContext($this->company, $this->branch, false));
        session(['active_company_id' => $this->company->id, 'active_branch_id' => $this->branch->id]);
    }

    public function test_hr_snapshot_surfaces_live_headcount(): void
    {
        $this->seedEmployees(3);

        $this->actingAs($this->ceo);
        $hr = app(ExecutiveHrIntelligenceService::class)->build();

        $this->assertTrue($hr['available']);
        $this->assertSame(3, $hr['employees_raw']);
        $this->assertSame('3', $hr['employees']);
    }

    public function test_asset_snapshot_surfaces_register_totals(): void
    {
        $this->seedFixedAsset(250000, 50000);

        $this->actingAs($this->ceo);
        $assets = app(ExecutiveAssetIntelligenceService::class)->build();

        $this->assertTrue($assets['available']);
        $this->assertSame(1, $assets['asset_count_raw']);
        $this->assertSame(200000.0, $assets['net_book_value_raw']);
    }

    public function test_dashboard_renders_hr_and_asset_snapshots(): void
    {
        $this->seedEmployees(2);
        $this->seedFixedAsset(100000, 10000);

        $response = $this->actingAs($this->ceo)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee(__('HR Snapshot'), false);
        $response->assertSee(__('Asset Snapshot'), false);
        $response->assertSee(__('Employees'), false);
        $response->assertSee(__('Net Book Value'), false);
    }

    public function test_drilldown_links_are_permission_gated(): void
    {
        $this->actingAs($this->ceo);

        $hr = app(ExecutiveHrIntelligenceService::class)->build();
        $assets = app(ExecutiveAssetIntelligenceService::class)->build();

        $hrRoutes = collect($hr['links'])->pluck('route')->all();
        $assetRoutes = collect($assets['links'])->pluck('route')->all();

        $this->assertContains('admin.hr.kpi', $hrRoutes);
        $this->assertContains('admin.employees.index', $hrRoutes);
        $this->assertContains('admin.assets.intelligence.executive', $assetRoutes);
        $this->assertContains('admin.assets.maintenance.dashboard', $assetRoutes);

        foreach ($hr['links'] as $link) {
            $this->assertStringContainsString(route($link['route']), $link['url']);
        }
    }

    public function test_presenter_includes_hr_and_asset_payloads(): void
    {
        $this->actingAs($this->ceo);

        $dashboard = app(ExecutiveDashboardPresenter::class)->build();

        $this->assertArrayHasKey('hr_snapshot', $dashboard);
        $this->assertArrayHasKey('asset_snapshot', $dashboard);
        $this->assertArrayHasKey('links', $dashboard['hr_snapshot']);
        $this->assertArrayHasKey('links', $dashboard['asset_snapshot']);
    }

    public function test_snapshots_hidden_without_module_permissions(): void
    {
        $viewer = User::factory()->create([
            'company_id' => $this->company->id,
            'default_branch_id' => $this->branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $role = Role::findByName('Viewer', 'web');
        $role->syncPermissions(['quotations.view']);
        $viewer->assignRole('Viewer');

        $this->actingAs($viewer);

        $hr = app(ExecutiveHrIntelligenceService::class)->build();
        $assets = app(ExecutiveAssetIntelligenceService::class)->build();

        $this->assertFalse($hr['visible']);
        $this->assertFalse($assets['visible']);
        $this->assertSame([], $hr['links']);
        $this->assertSame([], $assets['links']);
    }

    protected function seedEmployees(int $count): void
    {
        $department = Department::query()->where('company_id', $this->company->id)->firstOrFail();
        $jobTitle = JobTitle::query()->where('company_id', $this->company->id)->firstOrFail();

        for ($i = 1; $i <= $count; $i++) {
            Employee::query()->create([
                'company_id' => $this->company->id,
                'branch_id' => $this->branch->id,
                'department_id' => $department->id,
                'job_title_id' => $jobTitle->id,
                'employee_number' => 'EMP-'.str_pad((string) $i, 4, '0', STR_PAD_LEFT),
                'first_name' => 'Staff',
                'last_name' => "Member {$i}",
                'hire_date' => now()->subYear(),
                'employment_status' => EmploymentStatus::Active,
                'is_active' => true,
            ]);
        }
    }

    protected function seedFixedAsset(float $cost, float $accumulated): FixedAsset
    {
        $category = AssetCategory::query()->create([
            'company_id' => $this->company->id,
            'name' => 'Production Equipment',
            'code' => 'PROD-EQ',
            'is_active' => true,
        ]);

        return FixedAsset::query()->create([
            'company_id' => $this->company->id,
            'branch_id' => $this->branch->id,
            'asset_category_id' => $category->id,
            'asset_number' => 'FA-0001',
            'asset_name' => 'Heidelberg Press',
            'acquisition_date' => now()->subYears(2)->toDateString(),
            'acquisition_cost' => $cost,
            'residual_value' => 0,
            'accumulated_depreciation' => $accumulated,
            'status' => FixedAssetStatus::Active,
        ]);
    }
}
