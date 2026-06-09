<?php

namespace Tests\Feature\Reports;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionType;
use App\Enums\QualityCheckResult;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use App\Models\User;
use App\Support\Reports\Production360Presenter;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Production360Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_production_360_loads_executive_sections(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'intelligence.production.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.production360'))
            ->assertOk()
            ->assertSee(__('Production 360'), false)
            ->assertSee(__('Read-only intelligence'), false)
            ->assertSee(__('Production Summary'), false)
            ->assertSee(__('Branch Comparison'), false)
            ->assertSee(__('Quality Intelligence'), false)
            ->assertSee(__('Capacity Intelligence'), false)
            ->assertSee(__('Dispatch Intelligence'), false)
            ->assertSee(__('Trend Charts'), false)
            ->assertSee('Bottom Performers', false)
            ->assertDontSee(__('Create Job Card'), false);
    }

    public function test_branch_filter(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.production360', ['branch_id' => $branch->id]))
            ->assertOk();
    }

    public function test_presenter_builds_twelve_sections(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'intelligence.production.view']);

        $job = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::InProduction,
            'production_type' => ProductionType::Digital,
            'planned_end_date' => now()->subDays(3),
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $job->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Passed,
            'checked_at' => now(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.reports.production360'));

        $response->assertOk();
        $response->assertViewHas('sections', fn (array $sections) => count($sections) === 12);
        $response->assertViewHas('read_only', true);
        $response->assertViewHas('sections', fn (array $sections) => in_array(
            __('Delay Intelligence'),
            collect($sections)->pluck('title')->all(),
            true,
        ));
    }

    public function test_csv_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.intelligence360.export', ['reportKey' => 'production', 'format' => 'csv']))
            ->assertForbidden();
    }

    public function test_csv_export_streams_for_authorized_user(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'reports.export']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.intelligence360.export', ['reportKey' => 'production', 'format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    protected function tenantUser(array $permissions): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        Role::findByName('Viewer', 'web')->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
