<?php

namespace Tests\Feature\Admin;

use App\Enums\TrainingAssignmentStatus;
use App\Enums\TrainingProgramStatus;
use App\Enums\TrainingType;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\EmployeeSkill;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\TrainingEvaluation;
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

class TrainingProgramLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_program_can_be_created_with_lifecycle_fields(): void
    {
        $hr = $this->hrUser();

        $response = $this->actingAs($hr)->post(route('admin.hr.training.programs.store'), [
            'type' => TrainingType::Safety->value,
            'title' => 'Advanced Safety Training',
            'duration_hours' => 12,
            'budget_amount' => 5000,
            'scheduled_start_date' => now()->addWeek()->toDateString(),
            'scheduled_end_date' => now()->addWeeks(2)->toDateString(),
            'evaluation_instructions' => 'Rate effectiveness on a 0-100 scale.',
            'skill_tags' => 'Safety, Compliance',
            'status' => TrainingProgramStatus::Draft->value,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('training_programs', [
            'title' => 'Advanced Safety Training',
            'budget_amount' => 5000,
            'status' => TrainingProgramStatus::Draft->value,
        ]);
    }

    public function test_program_code_is_generated(): void
    {
        $companyId = Company::query()->where('code', 'JANA')->value('id');

        $program = app(TrainingProgramService::class)->create($companyId, [
            'type' => TrainingType::Internal->value,
            'title' => 'Onboarding',
            'status' => TrainingProgramStatus::Draft->value,
        ]);

        $this->assertNotNull($program->code);
        $this->assertStringStartsWith('TRP-'.now()->year.'-', $program->code);
    }

    public function test_program_can_be_viewed(): void
    {
        $hr = $this->hrUser();
        $program = $this->draftProgram($hr->company_id);

        $this->actingAs($hr)
            ->get(route('admin.hr.training.programs.show', $program))
            ->assertOk()
            ->assertSee($program->title)
            ->assertSee($program->code);
    }

    public function test_program_can_be_edited(): void
    {
        $hr = $this->hrUser();
        $program = $this->draftProgram($hr->company_id);

        $this->actingAs($hr)
            ->put(route('admin.hr.training.programs.update', $program), [
                'type' => TrainingType::Technical->value,
                'title' => 'Updated Program Title',
                'duration_hours' => 16,
                'status' => TrainingProgramStatus::Draft->value,
            ])
            ->assertRedirect(route('admin.hr.training.programs.show', $program));

        $this->assertDatabaseHas('training_programs', [
            'id' => $program->id,
            'title' => 'Updated Program Title',
            'duration_hours' => 16,
        ]);
    }

    public function test_draft_program_can_be_activated(): void
    {
        $hr = $this->hrUser();
        $program = $this->draftProgram($hr->company_id);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.programs.activate', $program))
            ->assertRedirect();

        $program->refresh();
        $this->assertSame(TrainingProgramStatus::Active, $program->status);
        $this->assertTrue($program->is_active);
    }

    public function test_active_program_can_be_deactivated(): void
    {
        $hr = $this->hrUser();
        $program = $this->activeProgram($hr->company_id);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.programs.deactivate', $program))
            ->assertRedirect();

        $program->refresh();
        $this->assertSame(TrainingProgramStatus::Draft, $program->status);
        $this->assertFalse($program->is_active);
    }

    public function test_active_program_can_be_completed(): void
    {
        $hr = $this->hrUser();
        $program = $this->activeProgram($hr->company_id);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.programs.complete', $program))
            ->assertRedirect();

        $program->refresh();
        $this->assertSame(TrainingProgramStatus::Completed, $program->status);
        $this->assertFalse($program->is_active);
    }

    public function test_completed_program_can_be_reopened(): void
    {
        $hr = $this->hrUser();
        $program = $this->activeProgram($hr->company_id);
        app(TrainingProgramService::class)->complete($program);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.programs.reopen', $program))
            ->assertRedirect();

        $program->refresh();
        $this->assertSame(TrainingProgramStatus::Active, $program->status);
        $this->assertTrue($program->is_active);
    }

    public function test_program_can_be_duplicated(): void
    {
        $hr = $this->hrUser();
        $program = $this->activeProgram($hr->company_id);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.programs.duplicate', $program))
            ->assertRedirect();

        $this->assertDatabaseHas('training_programs', [
            'duplicated_from_id' => $program->id,
            'status' => TrainingProgramStatus::Draft->value,
        ]);
    }

    public function test_program_can_be_archived(): void
    {
        $hr = $this->hrUser();
        $program = $this->activeProgram($hr->company_id);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.programs.archive', $program))
            ->assertRedirect();

        $program->refresh();
        $this->assertSame(TrainingProgramStatus::Archived, $program->status);
        $this->assertNotNull($program->archived_at);
    }

    public function test_archived_program_cannot_be_assigned(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $program = $this->activeProgram($employee->company_id);
        app(TrainingProgramService::class)->archive($program);

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(TrainingAssignmentService::class)->assign($employee->company_id, [
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
        ], $hr);
    }

    public function test_assignment_can_move_assigned_to_in_progress(): void
    {
        $hr = $this->hrUser();
        $assignment = $this->createAssignment($hr);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.assignments.start', $assignment))
            ->assertRedirect();

        $assignment->refresh();
        $this->assertSame(TrainingAssignmentStatus::InProgress, $assignment->status);
    }

    public function test_assignment_can_be_cancelled(): void
    {
        $hr = $this->hrUser();
        $assignment = $this->createAssignment($hr);
        app(TrainingAssignmentService::class)->start($assignment);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.assignments.cancel', $assignment))
            ->assertRedirect();

        $assignment->refresh();
        $this->assertSame(TrainingAssignmentStatus::Cancelled, $assignment->status);
    }

    public function test_completed_assignment_cannot_be_cancelled(): void
    {
        $hr = $this->hrUser();
        $assignment = $this->createAssignment($hr);
        app(TrainingAssignmentService::class)->complete($assignment, [], $hr);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.assignments.cancel', $assignment))
            ->assertSessionHasErrors();

        $assignment->refresh();
        $this->assertSame(TrainingAssignmentStatus::Completed, $assignment->status);
    }

    public function test_completing_assignment_syncs_skills(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $program = app(TrainingProgramService::class)->create($employee->company_id, [
            'type' => TrainingType::Technical->value,
            'title' => 'Skills Test Program',
            'skill_tags' => 'Quality Control, Finishing',
            'status' => TrainingProgramStatus::Active->value,
        ]);

        $assignment = app(TrainingAssignmentService::class)->assign($employee->company_id, [
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
        ], $hr);

        app(TrainingAssignmentService::class)->complete($assignment, [], $hr);

        $this->assertDatabaseHas('employee_skills', [
            'employee_id' => $employee->id,
            'skill_name' => 'Quality Control',
        ]);
        $this->assertGreaterThanOrEqual(2, EmployeeSkill::query()->where('employee_id', $employee->id)->count());
    }

    public function test_completing_certification_assignment_creates_expiry_tracking(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $program = app(TrainingProgramService::class)->create($employee->company_id, [
            'type' => TrainingType::Certification->value,
            'title' => 'Cert Program',
            'requires_certification' => true,
            'certificate_validity_days' => 180,
            'status' => TrainingProgramStatus::Active->value,
        ]);

        $assignment = app(TrainingAssignmentService::class)->assign($employee->company_id, [
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
        ], $hr);

        $completed = app(TrainingAssignmentService::class)->complete($assignment, [], $hr);

        $this->assertNotNull($completed->certificate_reference);
        $this->assertNotNull($completed->certificate_expires_at);
    }

    public function test_calendar_page_loads_scheduled_programs(): void
    {
        $hr = $this->hrUser();
        TrainingProgram::query()->create([
            'company_id' => $hr->company_id,
            'code' => 'TRP-2026-0001',
            'type' => TrainingType::Internal->value,
            'status' => TrainingProgramStatus::Active->value,
            'title' => 'Scheduled Workshop',
            'duration_hours' => 4,
            'scheduled_start_date' => now()->startOfMonth()->addDays(5),
            'scheduled_end_date' => now()->startOfMonth()->addDays(6),
            'is_active' => true,
        ]);

        $this->actingAs($hr)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.training.calendar', [
                'embedded' => '1',
                'month' => now()->month,
                'year' => now()->year,
            ]))
            ->assertOk()
            ->assertSee('Scheduled Workshop');
    }

    public function test_certificate_tracking_page_shows_expiring_certificates(): void
    {
        $hr = $this->hrUser();
        $employee = $this->testEmployee();
        $program = $this->activeProgram($employee->company_id, certification: true);

        EmployeeTrainingAssignment::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
            'reference' => 'TRN-CERT-001',
            'status' => TrainingAssignmentStatus::Completed,
            'hours_completed' => 8,
            'certificate_reference' => 'CERT-TRACK-001',
            'certificate_expires_at' => now()->addDays(10),
            'completed_at' => now()->subMonths(6),
        ]);

        $this->actingAs($hr)
            ->withHeaders(['Turbo-Frame' => 'module-workspace-content'])
            ->get(route('admin.hr.training.certificates', ['embedded' => '1', 'status' => 'expiring']))
            ->assertOk()
            ->assertSee('CERT-TRACK-001')
            ->assertSee($employee->full_name);
    }

    public function test_evaluation_can_be_submitted(): void
    {
        $hr = $this->hrUser();
        $program = $this->activeProgram($hr->company_id);

        $this->actingAs($hr)
            ->post(route('admin.hr.training.programs.evaluate', $program), [
                'score' => 85,
                'feedback' => 'Excellent program delivery.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('training_evaluations', [
            'training_program_id' => $program->id,
            'score' => 85,
            'evaluated_by_user_id' => $hr->id,
        ]);

        $this->assertSame(1, TrainingEvaluation::query()->where('training_program_id', $program->id)->count());
    }

    public function test_unauthorized_user_cannot_manage_training_lifecycle(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();
        $program = $this->activeProgram($company->id);

        $viewer = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);

        $role = Role::findOrCreate('Training Viewer Only', 'web');
        Permission::findOrCreate('hr.training.view', 'web');
        $role->syncPermissions(['hr.training.view']);
        $viewer->assignRole($role);

        $this->actingAs($viewer)
            ->post(route('admin.hr.training.programs.activate', $program))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->post(route('admin.hr.training.programs.archive', $program))
            ->assertForbidden();

        $this->actingAs($viewer)
            ->get(route('admin.hr.training.programs.create'))
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
            'employee_number' => 'EMP-LC-'.random_int(100, 999),
            'first_name' => 'Lifecycle',
            'last_name' => 'Staff',
            'email' => 'lifecycle.staff.'.random_int(100, 999).'@janaprints.local',
            'employment_status' => 'active',
            'is_active' => true,
        ]);
    }

    protected function draftProgram(int $companyId): TrainingProgram
    {
        return TrainingProgram::query()->create([
            'company_id' => $companyId,
            'code' => app(TrainingProgramService::class)->nextCode($companyId),
            'type' => TrainingType::Internal->value,
            'status' => TrainingProgramStatus::Draft->value,
            'title' => 'Draft Program',
            'duration_hours' => 8,
            'is_active' => false,
        ]);
    }

    protected function activeProgram(int $companyId, bool $certification = false): TrainingProgram
    {
        return TrainingProgram::query()->create([
            'company_id' => $companyId,
            'code' => app(TrainingProgramService::class)->nextCode($companyId),
            'type' => TrainingType::Safety->value,
            'status' => TrainingProgramStatus::Active->value,
            'title' => 'Active Program',
            'duration_hours' => 8,
            'requires_certification' => $certification,
            'certificate_validity_days' => $certification ? 365 : null,
            'skill_tags' => ['Safety Compliance'],
            'is_active' => true,
        ]);
    }

    protected function createAssignment(User $hr): EmployeeTrainingAssignment
    {
        $employee = $this->testEmployee();
        $program = $this->activeProgram($employee->company_id);

        return app(TrainingAssignmentService::class)->assign($employee->company_id, [
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
        ], $hr);
    }
}
