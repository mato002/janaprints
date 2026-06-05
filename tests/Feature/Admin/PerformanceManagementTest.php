<?php

namespace Tests\Feature\Admin;

use App\Enums\PerformanceRating;
use App\Enums\PerformanceReviewCycle;
use App\Enums\PerformanceReviewStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\EmployeeSalesTarget;
use App\Models\Hr\PerformanceReview;
use App\Models\User;
use App\Support\Hr\PerformanceReviewService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PerformanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_dashboard_renders_for_hr_user(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.performance.dashboard'))
            ->assertOk()
            ->assertSee(__('Performance Management'));
    }

    public function test_create_review_calculates_kpis_and_rating(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();

        foreach (['2026-01-02', '2026-01-03', '2026-01-06', '2026-01-07'] as $date) {
            AttendanceRecord::query()->create([
                'company_id' => $employee->company_id,
                'branch_id' => $employee->branch_id,
                'employee_id' => $employee->id,
                'attendance_date' => $date,
                'status' => 'present',
            ]);
        }

        EmployeeSalesTarget::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'period_start' => '2026-01-01',
            'period_end' => '2026-03-31',
            'target_amount' => 100000,
        ]);

        $review = app(PerformanceReviewService::class)->create($employee->company_id, [
            'employee_id' => $employee->id,
            'cycle' => PerformanceReviewCycle::Quarterly->value,
            'period_start' => '2026-01-01',
            'period_end' => '2026-03-31',
            'rating' => PerformanceRating::Good->value,
            'strengths' => 'Reliable attendance',
        ], $hr, submit: true);

        $this->assertDatabaseHas('performance_reviews', [
            'employee_id' => $employee->id,
            'cycle' => PerformanceReviewCycle::Quarterly->value,
            'rating' => PerformanceRating::Good->value,
            'status' => PerformanceReviewStatus::Submitted->value,
        ]);

        $this->assertGreaterThan(0, (float) $review->attendance_percent);
        $this->assertSame(100000.0, (float) $review->sales_target);
        $this->assertNotNull($review->composite_score);
    }

    public function test_submit_review_via_http(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();

        $review = PerformanceReview::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'reference' => 'APR-TEST-001',
            'cycle' => PerformanceReviewCycle::Annual->value,
            'period_start' => '2026-01-01',
            'period_end' => '2026-12-31',
            'status' => PerformanceReviewStatus::Draft->value,
            'rating' => PerformanceRating::Average->value,
        ]);

        $this->actingAs($hr)
            ->post(route('admin.hr.performance.submit', $review))
            ->assertRedirect();

        $review->refresh();
        $this->assertSame(PerformanceReviewStatus::Submitted, $review->status);
    }

    public function test_viewer_cannot_access_performance(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $viewer = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $viewer->assignRole(Role::findByName('Viewer', 'web'));

        $this->actingAs($viewer)
            ->get(route('admin.hr.performance.dashboard'))
            ->assertForbidden();
    }

    public function test_manage_forbidden_without_permission(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $employee = $this->testEmployee();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $role = Role::findOrCreate('Performance Viewer', 'web');
        Permission::findOrCreate('hr.performance.view', 'web');
        $role->syncPermissions(['hr.performance.view']);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.hr.performance.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.hr.performance.store'), [
                'employee_id' => $employee->id,
                'cycle' => PerformanceReviewCycle::Quarterly->value,
                'period_start' => '2026-01-01',
                'period_end' => '2026-03-31',
            ])
            ->assertForbidden();
    }

    protected function hrUser(): User
    {
        $user = User::factory()->create([
            'company_id' => Company::query()->where('code', 'JANA')->value('id'),
            'default_branch_id' => Branch::query()->where('code', 'HQ')->value('id'),
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $user->assignRole(Role::findByName('HR', 'web'));

        return $user;
    }

    protected function testEmployee(): Employee
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();

        return Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-PERF-002',
            'first_name' => 'Review',
            'last_name' => 'Candidate',
            'email' => 'review.candidate@janaprints.local',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }
}
