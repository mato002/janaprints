<?php

namespace Tests\Feature\Admin;

use App\Enums\TrainingAssignmentStatus;
use App\Enums\TrainingType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\EmployeeSkill;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\TrainingProgram;
use App\Models\User;
use App\Support\Hr\TrainingAssignmentService;
use App\Support\Hr\TrainingProgramService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrainingManagementTest extends TestCase
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
            ->get(route('admin.hr.training.dashboard'))
            ->assertOk()
            ->assertSee(__('Training & Development'));
    }

    public function test_training_assignment_creates_record(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $program = $this->testProgram($employee->company_id);

        $assignment = app(TrainingAssignmentService::class)->assign($employee->company_id, [
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
            'due_date' => now()->addMonth()->toDateString(),
        ], $hr);

        $this->assertDatabaseHas('employee_training_assignments', [
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
            'status' => TrainingAssignmentStatus::Assigned->value,
        ]);

        $this->assertStringStartsWith('TRN-', $assignment->reference);
    }

    public function test_completion_records_hours_and_certificate(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $program = $this->testProgram($employee->company_id, certification: true);

        $assignment = app(TrainingAssignmentService::class)->assign($employee->company_id, [
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
        ], $hr);

        $completed = app(TrainingAssignmentService::class)->complete($assignment, [
            'hours_completed' => 16,
            'certificate_reference' => 'CERT-SAFETY-001',
        ], $hr);

        $this->assertSame(TrainingAssignmentStatus::Completed, $completed->status);
        $this->assertSame(16.0, (float) $completed->hours_completed);
        $this->assertSame('CERT-SAFETY-001', $completed->certificate_reference);
        $this->assertNotNull($completed->certificate_expires_at);
    }

    public function test_completion_syncs_skills_matrix(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $program = app(TrainingProgramService::class)->create($employee->company_id, [
            'type' => TrainingType::Technical->value,
            'title' => 'Screen Printing Basics',
            'duration_hours' => 8,
            'skill_tags' => 'Screen Printing, Color Matching',
        ]);

        $assignment = app(TrainingAssignmentService::class)->assign($employee->company_id, [
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
        ], $hr);

        app(TrainingAssignmentService::class)->complete($assignment, [], $hr);

        $this->assertDatabaseHas('employee_skills', [
            'employee_id' => $employee->id,
            'skill_name' => 'Screen Printing',
        ]);
        $this->assertDatabaseHas('employee_skills', [
            'employee_id' => $employee->id,
            'skill_name' => 'Color Matching',
        ]);

        $this->assertGreaterThanOrEqual(2, EmployeeSkill::query()->where('employee_id', $employee->id)->count());
    }

    public function test_certification_expiry_surfaces_on_dashboard(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $program = $this->testProgram($employee->company_id, certification: true);

        $assignment = EmployeeTrainingAssignment::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
            'reference' => 'TRN-TEST-001',
            'status' => TrainingAssignmentStatus::Completed,
            'hours_completed' => 8,
            'certificate_reference' => 'CERT-EXP-001',
            'certificate_expires_at' => now()->addDays(14),
            'completed_at' => now()->subYear(),
        ]);

        $this->actingAs($hr)
            ->get(route('admin.hr.training.dashboard'))
            ->assertOk()
            ->assertSee('CERT-EXP-001')
            ->assertSee($assignment->employee->full_name);
    }

    public function test_viewer_cannot_access_training(): void
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
            ->get(route('admin.hr.training.dashboard'))
            ->assertForbidden();
    }

    public function test_manage_forbidden_without_permission(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $employee = $this->testEmployee();
        $program = $this->testProgram($employee->company_id);

        $user = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $role = Role::findOrCreate('Training Viewer', 'web');
        Permission::findOrCreate('hr.training.view', 'web');
        $role->syncPermissions(['hr.training.view']);
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('admin.hr.training.assignments.create'))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('admin.hr.training.assignments.store'), [
                'employee_id' => $employee->id,
                'training_program_id' => $program->id,
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
            'employee_number' => 'EMP-TRN-001',
            'first_name' => 'Training',
            'last_name' => 'Staff',
            'email' => 'training.staff@janaprints.local',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }

    protected function testProgram(int $companyId, bool $certification = false): TrainingProgram
    {
        return TrainingProgram::query()->create([
            'company_id' => $companyId,
            'type' => TrainingType::Safety->value,
            'title' => 'Workplace Safety',
            'duration_hours' => 8,
            'requires_certification' => $certification,
            'certificate_validity_days' => $certification ? 365 : null,
            'skill_tags' => ['Safety Compliance'],
            'is_active' => true,
        ]);
    }
}
