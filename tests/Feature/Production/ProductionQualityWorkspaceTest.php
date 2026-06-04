<?php

namespace Tests\Feature\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductionQualityWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_quality_workspace_requires_permission(): void
    {
        [, , $user, $job] = $this->qualityContext(permissions: ['production.view']);

        $this->actingAs($user)
            ->get(route('admin.production.quality.index'))
            ->assertForbidden();
    }

    public function test_quality_dashboard_renders_kpis_and_register(): void
    {
        [$company, $branch, $user, $job] = $this->qualityContext();

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $job->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Passed,
            'checked_at' => now(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $response = $this->actingAs($user)->get(route('admin.production.quality.index'));

        $response->assertOk();
        $response->assertSee(__('Quality Control'), false);
        $response->assertSee(__('Pending Inspections'), false);
        $response->assertSee(__('Passed'), false);
        $response->assertSee(__('Failed'), false);
        $response->assertSee(__('On Hold'), false);
        $response->assertSee(__('Inspection Register'), false);
        $response->assertSee($job->job_card_number, false);
    }

    public function test_quality_register_filters_by_result(): void
    {
        [$company, $branch, $user, $job] = $this->qualityContext();

        $failedJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $job->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Passed,
            'checked_at' => now(),
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $failedJob->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Failed,
            'checked_at' => now(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.quality.index', ['status' => 'failed']))
            ->assertOk()
            ->assertSee($failedJob->job_card_number, false)
            ->assertDontSee($job->job_card_number, false);
    }

    public function test_pending_inspection_filter_lists_jobs_awaiting_qc(): void
    {
        [$company, $branch, $user, $job] = $this->qualityContext();

        $job->update(['status' => ProductionJobCardStatus::QualityCheck]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.quality.index', ['status' => 'pending']))
            ->assertOk()
            ->assertSee(__('Pending'), false)
            ->assertSee($job->job_card_number, false)
            ->assertSee(__('Inspect'), false);
    }

    public function test_quality_intelligence_analytics_and_widgets_render(): void
    {
        [$company, $branch, $user, $job] = $this->qualityContext();

        $failedJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::OnHold,
        ]);

        $reworkJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::Rework,
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $job->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Passed,
            'checked_at' => now()->subHour(),
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $failedJob->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Failed,
            'comments' => 'Surface defect',
            'checked_at' => now(),
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $reworkJob->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::ReworkRequired,
            'checked_at' => now(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.quality.index'))
            ->assertOk()
            ->assertSee(__('Analytics'), false)
            ->assertSee(__('Pass Rate'), false)
            ->assertSee(__('Fail Rate'), false)
            ->assertSee(__('Rework Count'), false)
            ->assertSee(__('Hold Count'), false)
            ->assertSee(__('Recent Failures'), false)
            ->assertSee(__('Recent Holds'), false)
            ->assertSee(__('Jobs Requiring Rework'), false)
            ->assertSee($failedJob->job_card_number, false)
            ->assertSee($reworkJob->job_card_number, false)
            ->assertSee('Surface defect', false);
    }

    public function test_quality_search_filters_register(): void
    {
        [$company, $branch, $user, $job] = $this->qualityContext();

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
            ->get(route('admin.production.quality.index', ['search' => $job->job_card_number]))
            ->assertOk()
            ->assertSee($job->job_card_number, false);

        $this->actingAs($user)
            ->get(route('admin.production.quality.index', ['search' => 'NO-MATCH-XYZ']))
            ->assertOk()
            ->assertSee(__('No inspections recorded'), false);
    }

    public function test_quality_kpis_reflect_tenant_data(): void
    {
        [$company, $branch, $user, $job] = $this->qualityContext();

        $pendingJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::QualityCheck,
        ]);

        $holdJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'status' => ProductionJobCardStatus::OnHold,
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $job->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Passed,
            'checked_at' => now(),
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $holdJob->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Failed,
            'checked_at' => now(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.quality.index'))
            ->assertOk()
            ->assertViewHas('kpis', fn (array $kpis) => $kpis['pending_inspections'] === 1
                && $kpis['passed'] === 1
                && $kpis['failed'] === 1
                && $kpis['on_hold'] === 1);
    }

    public function test_quality_register_filters_by_date_and_inspector(): void
    {
        [$company, $branch, $user, $job] = $this->qualityContext();

        $otherUser = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
            'name' => 'Other Inspector',
        ]);

        $otherJob = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $job->id,
            'checked_by' => $user->id,
            'result' => QualityCheckResult::Passed,
            'comments' => 'Approved finish',
            'checked_at' => now()->subDays(2),
        ]);

        QualityCheck::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'production_job_card_id' => $otherJob->id,
            'checked_by' => $otherUser->id,
            'result' => QualityCheckResult::Passed,
            'checked_at' => now(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.production.quality.index', [
                'date' => now()->subDays(2)->toDateString(),
            ]))
            ->assertOk()
            ->assertSee($job->job_card_number, false)
            ->assertDontSee($otherJob->job_card_number, false);

        $this->actingAs($user)
            ->get(route('admin.production.quality.index', [
                'inspector' => $user->id,
            ]))
            ->assertOk()
            ->assertSee($job->job_card_number, false)
            ->assertDontSee($otherJob->job_card_number, false)
            ->assertSee('Approved finish', false);
    }

    public function test_register_links_to_job_360_when_route_exists(): void
    {
        [$company, $branch, $user, $job] = $this->qualityContext();

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
            ->get(route('admin.production.quality.index'))
            ->assertOk()
            ->assertSee(route('admin.production.job-cards.show', $job), false);
    }

    /**
     * @return array{0: Company, 1: Branch, 2: User, 3: ProductionJobCard}
     */
    protected function qualityContext(?array $permissions = null): array
    {
        $company = Company::factory()->create();
        $branch = Branch::factory()->create(['company_id' => $company->id]);

        $permissions ??= ['production.quality.view', 'production.view'];
        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
            'name' => 'QC Inspector',
        ]);
        $role = Role::findByName('Production', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole('Production');

        $job = ProductionJobCard::factory()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
        ]);

        return [$company, $branch, $user, $job];
    }
}
