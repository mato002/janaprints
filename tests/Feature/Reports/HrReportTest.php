<?php

namespace Tests\Feature\Reports;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveRequestStatus;
use App\Enums\PayrollRunStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HrReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_hr_reports_loads_analytics_catalog(): void
    {
        [$company, $branch, $user] = $this->reportUser(['hr.reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.hr'))
            ->assertOk()
            ->assertSee(__('HR Reports'), false)
            ->assertSee(__('Reporting Catalog'), false)
            ->assertSee(__('Attendance'), false)
            ->assertSee(__('Leave'), false)
            ->assertSee(__('Payroll'), false)
            ->assertSee(__('Workforce'), false)
            ->assertDontSee(__('No report data yet'), false);
    }

    public function test_attendance_report_shows_period_metrics(): void
    {
        [$company, $branch, $user] = $this->reportUser(['hr.reports.view']);
        $employee = $this->testEmployee();

        AttendanceRecord::query()->create([
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'employee_id' => $employee->id,
            'attendance_date' => now()->toDateString(),
            'status' => AttendanceStatus::Late,
            'late_minutes' => 15,
            'scheduled_hours' => 8,
            'actual_hours' => 8,
            'overtime_hours' => 1.5,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.hr', [
                'tab' => 'attendance',
                'from_date' => now()->startOfMonth()->toDateString(),
                'to_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee(__('Attendance Rate'), false)
            ->assertSee(__('Late Arrivals Report'), false)
            ->assertSee(__('Overtime Report'), false);
    }

    public function test_leave_report_shows_utilization(): void
    {
        [$company, $branch, $user] = $this->reportUser(['hr.reports.view']);
        $employee = $this->testEmployee();
        $leaveType = LeaveType::query()->where('code', 'ANNUAL')->firstOrFail();

        LeaveRequest::query()->create([
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'reference' => 'LR-TEST-001',
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDays(3),
            'days_requested' => 3,
            'status' => LeaveRequestStatus::Approved,
            'submitted_at' => now()->subWeek(),
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.hr', [
                'tab' => 'leave',
                'from_date' => now()->startOfMonth()->toDateString(),
                'to_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee(__('Leave Utilization by Type'), false)
            ->assertSee(__('Days Used'), false);
    }

    public function test_payroll_report_shows_cost_summary(): void
    {
        if (! Schema::hasTable('payroll_runs')) {
            $this->markTestSkipped('payroll_runs table is not migrated yet.');
        }

        [$company, $branch, $user] = $this->reportUser(['hr.reports.view']);

        PayrollRun::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'reference' => 'PR-HR-RPT-001',
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
            'pay_date' => now()->endOfMonth(),
            'status' => PayrollRunStatus::Posted,
            'employee_count' => 5,
            'gross_total' => 250000,
            'deductions_total' => 50000,
            'net_total' => 200000,
        ]);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.hr', [
                'tab' => 'payroll',
                'from_date' => now()->startOfMonth()->toDateString(),
                'to_date' => now()->endOfMonth()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee(__('Payroll Cost by Run'), false)
            ->assertSee(__('Gross Total'), false)
            ->assertSee('250,000.00', false);
    }

    public function test_csv_export_streams_for_active_tab(): void
    {
        [$company, $branch, $user] = $this->reportUser(['hr.reports.view', 'hr.reports.export']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.reports.hr.export'), [
                'tab' => 'attendance',
                'from_date' => now()->startOfMonth()->toDateString(),
                'to_date' => now()->toDateString(),
                'format' => 'csv',
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_export_requires_permission(): void
    {
        [$company, $branch, $user] = $this->reportUser(['hr.reports.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->post(route('admin.reports.hr.export'), [
                'tab' => 'attendance',
                'format' => 'csv',
            ])
            ->assertForbidden();
    }

    public function test_view_requires_permission(): void
    {
        [$company, $branch, $user] = $this->reportUser(['crm.customers.view']);

        session(['active_company_id' => $company->id, 'active_branch_id' => $branch->id]);

        $this->actingAs($user)
            ->get(route('admin.reports.hr'))
            ->assertForbidden();
    }

    /**
     * @param  list<string>  $permissions
     * @return array{0: Company, 1: Branch, 2: User}
     */
    protected function reportUser(array $permissions): array
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::findOrCreate('HR Report Viewer', 'web');
        $role->syncPermissions($permissions);
        $user->assignRole($role);

        return [$company, $branch, $user];
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
            'employee_number' => 'EMP-HR-RPT-001',
            'first_name' => 'Report',
            'last_name' => 'Subject',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }
}
