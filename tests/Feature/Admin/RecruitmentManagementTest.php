<?php

namespace Tests\Feature\Admin;

use App\Enums\OfferLetterStatus;
use App\Enums\OnboardingStatus;
use App\Enums\RecruitmentPipelineStage;
use App\Enums\VacancyStatus;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\JobApplication;
use App\Models\Hr\OfferLetter;
use App\Models\Hr\OnboardingRecord;
use App\Models\Hr\Vacancy;
use App\Models\User;
use App\Support\Hr\OnboardingService;
use App\Support\Hr\RecruitmentApplicationService;
use App\Support\Hr\VacancyService;
use Database\Seeders\OrganizationFoundationSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RecruitmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(OrganizationFoundationSeeder::class);
    }

    public function test_vacancy_creation_and_publish(): void
    {
        $hr = $this->hrUser();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();

        $vacancy = app(VacancyService::class)->create($company->id, [
            'title' => 'Screen Printer',
            'positions' => 2,
        ], $hr);

        $this->assertDatabaseHas('vacancies', [
            'title' => 'Screen Printer',
            'status' => VacancyStatus::Draft->value,
        ]);
        $this->assertStringStartsWith('VAC-', $vacancy->reference);

        app(VacancyService::class)->publish($vacancy->fresh());

        $this->assertSame(VacancyStatus::Open, $vacancy->fresh()->status);
    }

    public function test_application_workflow_advances_pipeline(): void
    {
        $hr = $this->hrUser();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $vacancy = $this->openVacancy($company->id, $hr);

        $application = app(RecruitmentApplicationService::class)->apply($company->id, [
            'vacancy_id' => $vacancy->id,
            'first_name' => 'Jane',
            'last_name' => 'Candidate',
            'email' => 'jane.candidate@janaprints.local',
        ], $hr);

        $this->assertSame(RecruitmentPipelineStage::Applied, $application->stage);

        $advanced = app(RecruitmentApplicationService::class)->advanceStage(
            $application,
            RecruitmentPipelineStage::Screening,
        );

        $this->assertSame(RecruitmentPipelineStage::Screening, $advanced->stage);
    }

    public function test_interview_scheduling_moves_to_interview_stage(): void
    {
        $hr = $this->hrUser();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $vacancy = $this->openVacancy($company->id, $hr);

        $application = app(RecruitmentApplicationService::class)->apply($company->id, [
            'vacancy_id' => $vacancy->id,
            'first_name' => 'John',
            'last_name' => 'Applicant',
        ], $hr);

        $schedule = app(RecruitmentApplicationService::class)->scheduleInterview($application, [
            'scheduled_at' => now()->addDays(3)->toDateTimeString(),
            'location' => 'HQ Conference Room',
        ], $hr);

        $this->assertDatabaseHas('interview_schedules', [
            'job_application_id' => $application->id,
            'location' => 'HQ Conference Room',
        ]);

        $application->refresh();
        $this->assertSame(RecruitmentPipelineStage::Interview, $application->stage);
        $this->assertNotNull($schedule->scheduled_at);
    }

    public function test_offer_letter_send_and_accept(): void
    {
        $hr = $this->hrUser();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $vacancy = $this->openVacancy($company->id, $hr);

        $application = app(RecruitmentApplicationService::class)->apply($company->id, [
            'vacancy_id' => $vacancy->id,
            'first_name' => 'Offer',
            'last_name' => 'Candidate',
        ], $hr);

        $offer = app(RecruitmentApplicationService::class)->createOffer($application, [
            'salary_offered' => 75000,
            'start_date' => now()->addMonth()->toDateString(),
        ], $hr);

        $this->assertSame(RecruitmentPipelineStage::Offer, $application->fresh()->stage);
        $this->assertStringStartsWith('OFF-', $offer->reference);

        app(RecruitmentApplicationService::class)->sendOffer($offer);
        $this->assertSame(OfferLetterStatus::Sent, $offer->fresh()->status);

        app(RecruitmentApplicationService::class)->acceptOffer($offer->fresh());
        $this->assertSame(OfferLetterStatus::Accepted, $offer->fresh()->status);
        $this->assertSame(RecruitmentPipelineStage::Accepted, $application->fresh()->stage);
    }

    public function test_onboarding_creates_employee(): void
    {
        $hr = $this->hrUser();
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->where('code', 'HQ')->firstOrFail();
        $department = Department::query()->where('company_id', $company->id)->firstOrFail();
        $vacancy = $this->openVacancy($company->id, $hr);

        $application = app(RecruitmentApplicationService::class)->apply($company->id, [
            'vacancy_id' => $vacancy->id,
            'first_name' => 'New',
            'last_name' => 'Hire',
            'email' => 'new.hire@janaprints.local',
        ], $hr);

        $offer = app(RecruitmentApplicationService::class)->createOffer($application, [
            'salary_offered' => 50000,
        ], $hr);
        app(RecruitmentApplicationService::class)->sendOffer($offer);
        app(RecruitmentApplicationService::class)->acceptOffer($offer->fresh());

        $onboarding = app(OnboardingService::class)->start($application->fresh(), $hr);

        app(OnboardingService::class)->update($onboarding, [
            'branch_id' => $branch->id,
            'department_id' => $department->id,
            'employee_number' => 'EMP-REC-001',
            'documents_collected' => true,
            'system_access_granted' => true,
        ]);

        $completed = app(OnboardingService::class)->complete($onboarding->fresh(), $hr);

        $this->assertSame(OnboardingStatus::Completed, $completed->status);
        $this->assertNotNull($completed->employee_id);
        $this->assertDatabaseHas('employees', [
            'employee_number' => 'EMP-REC-001',
            'first_name' => 'New',
            'last_name' => 'Hire',
        ]);
        $this->assertSame(RecruitmentPipelineStage::Hired, $application->fresh()->stage);
    }

    public function test_permissions_gate_recruitment_actions(): void
    {
        $company = Company::query()->where('code', 'JANA')->firstOrFail();
        $branch = Branch::query()->where('company_id', $company->id)->firstOrFail();

        $viewer = User::factory()->create([
            'company_id' => $company->id,
            'default_branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $role = Role::findOrCreate('Recruitment Viewer', 'web');
        Permission::findOrCreate('hr.recruitment.view', 'web');
        $role->syncPermissions(['hr.recruitment.view']);
        $viewer->assignRole($role);

        $this->actingAs($viewer)
            ->get(route('admin.hr.recruitment.dashboard'))
            ->assertOk();

        $this->actingAs($viewer)
            ->get(route('admin.hr.recruitment.vacancies.create'))
            ->assertForbidden();

        $hr = $this->hrUser();
        $this->actingAs($hr)
            ->get(route('admin.hr.recruitment.vacancies.create'))
            ->assertOk();
    }

    public function test_http_vacancy_and_application_flow(): void
    {
        $hr = $this->hrUser();

        $this->actingAs($hr)
            ->post(route('admin.hr.recruitment.vacancies.store'), [
                'title' => 'Production Assistant',
                'positions' => 1,
            ])
            ->assertRedirect();

        $vacancy = Vacancy::query()->where('title', 'Production Assistant')->firstOrFail();

        $this->actingAs($hr)
            ->post(route('admin.hr.recruitment.vacancies.publish', $vacancy))
            ->assertRedirect();

        $this->actingAs($hr)
            ->post(route('admin.hr.recruitment.applications.store'), [
                'vacancy_id' => $vacancy->id,
                'first_name' => 'HTTP',
                'last_name' => 'Applicant',
                'email' => 'http.applicant@janaprints.local',
            ])
            ->assertRedirect();

        $application = JobApplication::query()->whereHas('candidate', fn ($q) => $q->where('email', 'http.applicant@janaprints.local'))->first();
        $this->assertNotNull($application);

        $this->actingAs($hr)
            ->get(route('admin.hr.recruitment.applications.show', $application))
            ->assertOk()
            ->assertSee('HTTP Applicant');
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

    protected function openVacancy(int $companyId, User $user): Vacancy
    {
        $vacancy = app(VacancyService::class)->create($companyId, [
            'title' => 'Test Vacancy '.uniqid(),
            'positions' => 1,
        ], $user);

        return app(VacancyService::class)->publish($vacancy);
    }
}
