<?php

namespace Tests\Feature\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\PayrollRunStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\Hr\HrKpiScope;
use App\Support\Hr\HrWorkforceIntelligenceService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HrKpiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_workforce_intelligence_dashboard_renders(): void
    {
        $this->actingAs($this->kpiUser())
            ->get(route('admin.hr.kpi'))
            ->assertOk()
            ->assertSee(__('Workforce Intelligence'))
            ->assertSee(__('Attendance %'))
            ->assertSee(__('Rankings'))
            ->assertSee(__('Company'))
            ->assertSee(__('Branch'))
            ->assertSee(__('Department'))
            ->assertSee(__('Supervisor'));
    }

    public function test_kpi_calculation_returns_real_metrics(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();
        $employee = Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-KPI-001',
            'first_name' => 'KPI',
            'last_name' => 'Worker',
            'employment_status' => 'active',
            'is_active' => true,
        ]);

        AttendanceRecord::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_id' => $employee->id,
            'attendance_date' => now()->toDateString(),
            'status' => AttendanceStatus::Present,
            'scheduled_hours' => 8,
            'actual_hours' => 8,
            'overtime_hours' => 2,
        ]);

        PayrollRun::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'reference' => 'PR-KPI-001',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'pay_date' => now()->endOfMonth(),
            'status' => PayrollRunStatus::Posted,
            'employee_count' => 1,
            'gross_total' => 50000,
            'deductions_total' => 10000,
            'net_total' => 40000,
        ]);

        $scope = new HrKpiScope(
            companyId: $company->id,
            branchId: null,
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );

        $kpis = app(HrWorkforceIntelligenceService::class)->kpis($scope);
        $attendance = collect($kpis)->firstWhere('label', __('Attendance %'));

        $this->assertNotNull($attendance);
        $this->assertNotSame('0%', $attendance['value']);
        $this->assertSame('good', $attendance['status']);
    }

    public function test_rankings_include_top_attendance(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $scope = new HrKpiScope(
            companyId: $company->id,
            branchId: null,
            fromDate: now()->startOfMonth()->toDateString(),
            toDate: now()->toDateString(),
        );

        $rankings = app(HrWorkforceIntelligenceService::class)->rankings($scope);

        $this->assertArrayHasKey('top_attendance', $rankings);
        $this->assertSame(__('Top Attendance'), $rankings['top_attendance']['title']);
        $this->assertArrayHasKey('top_performers', $rankings);
        $this->assertArrayHasKey('top_departments', $rankings);
    }

    public function test_department_dimension_breakdown(): void
    {
        $this->actingAs($this->kpiUser())
            ->get(route('admin.hr.kpi', ['dimension' => 'department']))
            ->assertOk()
            ->assertSee(__('Dashboard'))
            ->assertSee(__('Attendance %'));
    }

    public function test_csv_export_streams(): void
    {
        $user = $this->kpiUser();
        Permission::findOrCreate('hr.kpi.export', 'web');
        $user->givePermissionTo('hr.kpi.export');

        $this->actingAs($user)
            ->post(route('admin.hr.kpi.export'), [
                'from_date' => now()->startOfMonth()->toDateString(),
                'to_date' => now()->toDateString(),
                'format' => 'csv',
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_view_requires_permission(): void
    {
        $company = Company::query()->first();
        $role = Role::findOrCreate('No KPI', 'web');
        $role->syncPermissions([]);
        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.hr.kpi'))
            ->assertForbidden();
    }

    public function test_export_requires_permission(): void
    {
        $company = Company::query()->first();
        $role = Role::findOrCreate('KPI View Only', 'web');
        Permission::findOrCreate('hr.kpi.view', 'web');
        $role->syncPermissions(['hr.kpi.view']);

        $user = User::factory()->create(['company_id' => $company->id]);
        $user->assignRole($role);

        $this->actingAs($user)
            ->post(route('admin.hr.kpi.export'), ['format' => 'csv'])
            ->assertForbidden();
    }

    protected function kpiUser(): User
    {
        $user = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'HR'))->first()
            ?? User::factory()->create(['company_id' => Company::query()->value('id')]);

        if (! $user->hasRole('HR')) {
            $user->assignRole(Role::findByName('HR', 'web'));
        }

        return $user;
    }
}
