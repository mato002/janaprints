<?php

namespace Tests\Feature\Reports;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionType;
use App\Enums\QualityCheckResult;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\JobCostSheet;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_production_reports_loads_report_workspace(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'reports.export']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.production', $this->embeddedQuery()))
            ->assertOk()
            ->assertSee(__('Production Reports'), false)
            ->assertSee(__('Production Throughput'), false)
            ->assertSee(__('Quality Reports'), false)
            ->assertSee(__('Material Reports'), false)
            ->assertSee(__('Dispatch Reports'), false)
            ->assertSee(__('Profitability Reports'), false)
            ->assertDontSee(__('Reporting Catalog'), false)
            ->assertDontSee(__('Historical reports'), false)
            ->assertDontSee(__('No report data yet'), false)
            ->assertDontSee(__('Read-only intelligence'), false)
            ->assertDontSee(__('Branch Comparison'), false)
            ->assertDontSee(__('Trend Charts'), false);
    }

    public function test_throughput_tab_shows_period_metrics(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::Completed,
            'production_type' => ProductionType::Digital,
            'actual_start_date' => now()->subDays(5),
            'actual_end_date' => now()->subDay(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.production', $this->embeddedQuery([
                'tab' => 'throughput',
                'from_date' => now()->subMonth()->toDateString(),
                'to_date' => now()->toDateString(),
            ])))
            ->assertOk()
            ->assertSee(__('Jobs Completed'), false)
            ->assertSee(__('Department Throughput'), false)
            ->assertSee(__('Machine Utilization'), false);
    }

    public function test_quality_tab_shows_detail_tables(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        $job = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::InProduction,
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

        $this->actingAs($user)
            ->get(route('admin.reports.production', $this->embeddedQuery(['tab' => 'quality'])))
            ->assertOk()
            ->assertSee(__('Pass Rate'), false)
            ->assertSee(__('Fail Rate'), false)
            ->assertSee(__('Rework Rate'), false)
            ->assertSee(__('Hold Rate'), false)
            ->assertSee(__('Quality Fail Reasons'), false)
            ->assertSee(__('Rework Summary'), false);
    }

    public function test_csv_export_streams_for_active_tab(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'reports.export']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.reports.production.export', [
                'tab' => 'throughput',
                'from_date' => '2026-01-01',
                'to_date' => '2026-06-05',
                'format' => 'csv',
            ]))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_scheduled_export_saves_preference(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view', 'reports.export']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.reports.production.export', [
                'tab' => 'dispatch',
                'schedule' => 1,
                'frequency' => 'weekly',
                'format' => 'csv',
            ]))
            ->assertRedirect(route('admin.reports.production', ['tab' => 'dispatch']))
            ->assertSessionHas('status');
    }

    public function test_presenter_builds_five_report_tabs(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.production', $this->embeddedQuery()))
            ->assertOk()
            ->assertViewHas('tabs', fn (array $tabs) => count($tabs) === 5)
            ->assertViewHas('active_tab', 'throughput')
            ->assertViewHas('tab_data', fn (array $data) => count($data['sections'] ?? []) >= 3);
    }

    public function test_profitability_tab_uses_job_cost_sheets(): void
    {
        [$company, $branch, $user] = $this->tenantUser(['reports.view']);

        $job = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::Completed,
        ]);

        JobCostSheet::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $job->id,
            'revenue' => 10000,
            'total_cost' => 6000,
            'gross_profit' => 4000,
            'gross_margin_percent' => 40,
            'calculated_at' => now(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.production', $this->embeddedQuery(['tab' => 'profitability'])))
            ->assertOk()
            ->assertSee(__('Job Profitability'), false)
            ->assertSee(__('Customer Profitability'), false);
    }

    /**
     * @param  array<string, mixed>  $params
     * @return array<string, mixed>
     */
    protected function embeddedQuery(array $params = []): array
    {
        return array_merge(['embedded' => '1'], $params);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
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

        $role = Role::findByName('Viewer', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Viewer');

        return [$company, $branch, $user];
    }
}
