<?php

namespace Tests\Feature\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\LeaveRequestStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\LeaveType;
use App\Models\User;
use App\Support\Hr\LeaveBalanceService;
use App\Support\Hr\LeaveRequestService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LeaveManagementTest extends TestCase
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
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.leave.dashboard'))
            ->assertOk()
            ->assertSee(__('Leave Management'))
            ->assertSee(__('All requests'));
    }

    public function test_leave_requests_index_renders(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.leave.index'))
            ->assertRedirect();

        $this->actingAs($this->hrUser())
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.leave.dashboard', ['tab' => 'requests']))
            ->assertOk()
            ->assertSee(__('All requests'));
    }

    public function test_leave_workspace_tabs_render_on_dashboard(): void
    {
        $user = $this->hrUser();

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.leave.dashboard', ['tab' => 'balances']))
            ->assertOk()
            ->assertSee(__('Leave balances'));

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.leave.dashboard', ['tab' => 'calendar']))
            ->assertOk()
            ->assertSee(__('Monthly'));

        $this->actingAs($user)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.leave.dashboard', ['tab' => 'setup']))
            ->assertOk()
            ->assertSee(__('Setup'))
            ->assertSee(__('Leave Types'));
    }

    public function test_apply_leave_form_renders(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.leave.create'))
            ->assertOk()
            ->assertSee(__('Apply for Leave'));
    }

    public function test_apply_leave_submits_request(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();

        $this->actingAs($hr)
            ->post(route('admin.hr.leave.store'), [
                'employee_id' => $employee->id,
                'leave_type_id' => LeaveType::query()->where('code', 'ANNUAL')->value('id'),
                'start_date' => now()->addDays(7)->toDateString(),
                'end_date' => now()->addDays(9)->toDateString(),
                'reason' => 'Family vacation',
                'submit' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'employee_id' => $employee->id,
            'status' => LeaveRequestStatus::Submitted->value,
            'days_requested' => 3,
        ]);
    }

    public function test_approve_leave_updates_attendance(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $leaveType = LeaveType::query()->where('code', 'ANNUAL')->firstOrFail();
        $start = now()->addDay();
        $end = $start->copy()->addDays(2);

        $request = app(LeaveRequestService::class)->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'reason' => 'Approved leave test',
        ], $hr, submit: true);

        $this->actingAs($hr)->post(route('admin.hr.leave.approve.supervisor', $request))->assertRedirect();
        $this->actingAs($hr)->post(route('admin.hr.leave.approve.hr', $request->fresh()))->assertRedirect();

        $request->refresh();
        $this->assertSame(LeaveRequestStatus::Approved, $request->status);

        $this->assertEquals(3, AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->where('leave_request_id', $request->id)
            ->where('status', AttendanceStatus::Leave->value)
            ->count());
    }

    public function test_reject_leave_releases_pending_balance(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $leaveType = LeaveType::query()->where('code', 'ANNUAL')->firstOrFail();
        $service = app(LeaveRequestService::class);

        $request = $service->create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => now()->addDays(14)->toDateString(),
            'end_date' => now()->addDays(15)->toDateString(),
            'reason' => 'Reject test',
        ], $hr, submit: true);

        $balance = app(LeaveBalanceService::class)->balanceFor($employee, $leaveType);
        $this->assertEquals(2, (float) $balance->fresh()->pending);

        $this->actingAs($hr)
            ->post(route('admin.hr.leave.reject', $request), [
                'rejection_reason' => 'Peak production period',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('leave_requests', [
            'id' => $request->id,
            'status' => LeaveRequestStatus::Rejected->value,
        ]);

        $this->assertEquals(0, (float) $balance->fresh()->pending);
    }

    public function test_balance_calculation(): void
    {
        $employee = $this->testEmployee();
        $leaveType = LeaveType::query()->where('code', 'ANNUAL')->firstOrFail();
        $balanceService = app(LeaveBalanceService::class);

        $balance = $balanceService->balanceFor($employee, $leaveType);
        $summary = $balanceService->summary($balance);

        $this->assertArrayHasKey('available', $summary);
        $this->assertGreaterThan(0, $summary['opening_balance']);
        $this->assertEquals(
            round($summary['opening_balance'] + $summary['earned'] - $summary['taken'] - $summary['pending'], 1),
            $summary['available'],
        );
    }

    public function test_calendar_view_renders(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.leave.calendar', ['view' => 'month']))
            ->assertRedirect();

        $this->actingAs($this->hrUser())
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.leave.dashboard', ['tab' => 'calendar', 'view' => 'week']))
            ->assertOk()
            ->assertSee(__('Weekly'));
    }

    public function test_viewer_cannot_access_leave_dashboard(): void
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
            ->get(route('admin.hr.leave.dashboard'))
            ->assertForbidden();
    }

    public function test_export_forbidden_without_permission(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $role = Role::findOrCreate('Leave Viewer', 'web');
        Permission::findOrCreate('hr.leave.view', 'web');
        $role->syncPermissions(['hr.leave.view']);
        $user->assignRole($role);

        $this->actingAs($user)
            ->post(route('admin.hr.leave.export'), ['format' => 'csv'])
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
            'employee_number' => 'EMP-LV-001',
            'first_name' => 'Leave',
            'last_name' => 'Tester',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }
}
