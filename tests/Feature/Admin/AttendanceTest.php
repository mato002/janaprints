<?php

namespace Tests\Feature\Admin;

use App\Enums\AttendanceCorrectionType;
use App\Enums\AttendanceStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\AttendanceRecord;
use App\Models\Hr\Shift;
use App\Models\User;
use App\Support\Hr\AttendanceService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AttendanceTest extends TestCase
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
            ->get(route('admin.hr.attendance.dashboard'))
            ->assertOk()
            ->assertSee(__('Attendance'))
            ->assertSee(__('Present Today'))
            ->assertSee(__('Register'));
    }

    public function test_register_renders_seeded_shifts(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.hr.attendance.index'))
            ->assertRedirect();

        $this->actingAs($this->hrUser())
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.attendance.dashboard', ['tab' => 'register']))
            ->assertOk()
            ->assertSee(__('Register'));
    }

    public function test_attendance_workspace_tabs_include_shifts(): void
    {
        $this->actingAs($this->hrUser())
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.attendance.dashboard', ['tab' => 'shifts']))
            ->assertOk()
            ->assertSee(__('Shifts'));
    }

    public function test_workspace_links_attendance_module(): void
    {
        $this->actingAs($this->hrUser())
            ->get(route('admin.workspaces.hr.section', [
                'section' => 'people',
                'tab' => 'attendance',
            ]))
            ->assertOk()
            ->assertSee(__('Attendance'))
            ->assertSee(route('admin.hr.attendance.dashboard', ['embedded' => '1'], false));
    }

    public function test_clock_in_records_attendance(): void
    {
        [$user, $employee, $shift] = $this->employeeUser();

        $service = app(AttendanceService::class);
        $request = Request::create('/', 'POST', ['shift_id' => $shift->id]);
        $request->setUserResolver(fn () => $user);

        $record = $service->clockIn($employee, $user, $request);

        $this->assertNotNull($record->clock_in_at);
        $this->assertSame($shift->id, $record->shift_id);
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employee->id,
            'shift_id' => $shift->id,
        ]);
    }

    public function test_clock_out_calculates_hours_and_overtime(): void
    {
        [$user, $employee, $shift] = $this->employeeUser();
        $service = app(AttendanceService::class);

        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-06-05 08:00:00'));

        $clockInRequest = Request::create('/', 'POST', ['shift_id' => $shift->id]);
        $clockInRequest->setUserResolver(fn () => $user);
        $service->clockIn($employee, $user, $clockInRequest);

        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::parse('2026-06-05 19:00:00'));

        $clockOutRequest = Request::create('/', 'POST');
        $clockOutRequest->setUserResolver(fn () => $user);

        $completed = $service->clockOut($employee, $user, $clockOutRequest);

        $this->assertNotNull($completed->clock_out_at);
        $this->assertGreaterThan(8, (float) $completed->actual_hours);
        $this->assertGreaterThan(0, (float) $completed->overtime_hours);

        \Illuminate\Support\Carbon::setTestNow();
    }

    public function test_manual_attendance_entry(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();

        $this->actingAs($hr)
            ->post(route('admin.hr.attendance.store'), [
                'employee_id' => $employee->id,
                'attendance_date' => now()->toDateString(),
                'status' => AttendanceStatus::Present->value,
                'clock_in_at' => now()->setTime(8, 0)->format('Y-m-d H:i:s'),
                'clock_out_at' => now()->setTime(17, 0)->format('Y-m-d H:i:s'),
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $employee->id,
            'status' => AttendanceStatus::Present->value,
        ]);
    }

    public function test_attendance_adjustment_creates_correction(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $record = AttendanceRecord::query()->create([
            'company_id' => $employee->company_id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'employee_id' => $employee->id,
            'attendance_date' => now()->toDateString(),
            'status' => AttendanceStatus::Absent,
            'scheduled_hours' => 8,
        ]);

        $this->actingAs($hr)
            ->post(route('admin.hr.attendance.adjust.store', $record), [
                'correction_type' => AttendanceCorrectionType::MissingClockOut->value,
                'reason' => 'Employee forgot to clock out.',
                'clock_in_at' => now()->setTime(8, 0)->format('Y-m-d H:i:s'),
                'clock_out_at' => now()->setTime(17, 0)->format('Y-m-d H:i:s'),
                'status' => AttendanceStatus::Present->value,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('attendance_corrections', [
            'attendance_record_id' => $record->id,
            'correction_type' => AttendanceCorrectionType::MissingClockOut->value,
        ]);

        $record->refresh();
        $this->assertSame(AttendanceStatus::Present, $record->status);
    }

    public function test_export_csv_is_allowed_with_permission(): void
    {
        $this->actingAs($this->hrUser())
            ->post(route('admin.hr.attendance.export'), [
                'format' => 'csv',
                'date' => now()->toDateString(),
            ])
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_viewer_cannot_access_attendance_dashboard(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $viewer = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
        $viewer->assignRole(Role::findByName('Viewer', 'web'));

        $this->actingAs($viewer)
            ->get(route('admin.hr.attendance.dashboard'))
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
            'is_active' => true,
        ]);

        $role = Role::findOrCreate('Attendance Viewer', 'web');
        Permission::findOrCreate('hr.attendance.view', 'web');
        $role->syncPermissions(['hr.attendance.view']);
        $user->assignRole($role);

        $this->actingAs($user)
            ->post(route('admin.hr.attendance.export'), ['format' => 'csv'])
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

    /**
     * @return array{0: User, 1: Employee, 2: Shift}
     */
    protected function employeeUser(): array
    {
        $employee = $this->testEmployee();
        $shift = Shift::query()->where('code', 'DAY')->firstOrFail();

        $user = User::factory()->create([
            'company_id' => $employee->company_id,
            'default_branch_id' => $employee->branch_id,
            'employee_id' => $employee->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        foreach (['hr.attendance.view', 'hr.attendance.create'] as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $role = Role::findOrCreate('Clock User', 'web');
        $role->syncPermissions(['hr.attendance.view', 'hr.attendance.create']);
        $user->assignRole($role);

        return [$user, $employee, $shift];
    }

    protected function testEmployee(): Employee
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();
        $shift = Shift::query()->where('company_id', $company->id)->where('code', 'DAY')->firstOrFail();

        return Employee::query()->create([
            'company_id' => $company->id,
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'shift_id' => $shift->id,
            'employee_number' => 'EMP-ATT-001',
            'first_name' => 'Attendance',
            'last_name' => 'Tester',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }
}
