<?php

namespace App\Support\Hr;

use App\Enums\EmploymentStatus;
use App\Enums\OnboardingStatus;
use App\Enums\RecruitmentPipelineStage;
use App\Enums\VacancyStatus;
use App\Models\Employee;
use App\Models\Hr\JobApplication;
use App\Models\Hr\OnboardingRecord;
use App\Models\User;
use App\Support\Organization\JobTitleService;
use Illuminate\Validation\ValidationException;

class OnboardingService
{
    public function start(JobApplication $application, User $user): OnboardingRecord
    {
        if ($application->stage !== RecruitmentPipelineStage::Accepted) {
            throw ValidationException::withMessages([
                'stage' => __('Onboarding can only start after offer acceptance.'),
            ]);
        }

        if ($application->onboarding) {
            return $application->onboarding;
        }

        $vacancy = $application->vacancy;

        return OnboardingRecord::query()->create([
            'company_id' => $application->company_id,
            'job_application_id' => $application->id,
            'status' => OnboardingStatus::Pending,
            'branch_id' => $vacancy->branch_id,
            'department_id' => $vacancy->department_id,
            'job_title_id' => $vacancy->job_title_id,
            'hire_date' => now()->toDateString(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(OnboardingRecord $record, array $data): OnboardingRecord
    {
        if ($record->status === OnboardingStatus::Completed) {
            throw ValidationException::withMessages([
                'status' => __('Completed onboarding cannot be edited.'),
            ]);
        }

        $record->update([
            'status' => OnboardingStatus::InProgress,
            'branch_id' => $data['branch_id'] ?? $record->branch_id,
            'department_id' => $data['department_id'] ?? $record->department_id,
            'job_title_id' => $data['job_title_id'] ?? $record->job_title_id,
            'supervisor_employee_id' => $data['supervisor_employee_id'] ?? $record->supervisor_employee_id,
            'employee_number' => $data['employee_number'] ?? $record->employee_number,
            'hire_date' => $data['hire_date'] ?? $record->hire_date,
            'documents_collected' => filter_var($data['documents_collected'] ?? $record->documents_collected, FILTER_VALIDATE_BOOLEAN),
            'system_access_granted' => filter_var($data['system_access_granted'] ?? $record->system_access_granted, FILTER_VALIDATE_BOOLEAN),
            'notes' => $data['notes'] ?? $record->notes,
        ]);

        return $record->fresh();
    }

    public function complete(OnboardingRecord $record, User $user): OnboardingRecord
    {
        if ($record->status === OnboardingStatus::Completed) {
            throw ValidationException::withMessages(['status' => __('Onboarding is already completed.')]);
        }

        if (! $record->employee_number) {
            throw ValidationException::withMessages([
                'employee_number' => __('Employee number is required to complete onboarding.'),
            ]);
        }

        if (! $record->branch_id) {
            throw ValidationException::withMessages([
                'branch_id' => __('Branch assignment is required.'),
            ]);
        }

        $application = $record->application;
        $candidate = $application->candidate;

        $employee = Employee::query()->create([
            'company_id' => $record->company_id,
            'branch_id' => $record->branch_id,
            'department_id' => $record->department_id,
            'job_title_id' => $record->job_title_id,
            'employee_number' => $record->employee_number,
            'first_name' => $candidate->first_name,
            'last_name' => $candidate->last_name,
            'email' => $candidate->email,
            'phone' => $candidate->phone,
            'hire_date' => $record->hire_date ?? now()->toDateString(),
            'employment_status' => EmploymentStatus::Active,
            'is_active' => true,
        ]);

        app(JobTitleService::class)->syncEmployeeDesignation($employee);

        $record->update([
            'employee_id' => $employee->id,
            'status' => OnboardingStatus::Completed,
            'completed_at' => now(),
            'completed_by_user_id' => $user->id,
        ]);

        $application->update([
            'stage' => RecruitmentPipelineStage::Hired,
            'employee_id' => $employee->id,
        ]);

        $vacancy = $application->vacancy;
        $vacancy->increment('filled_count');

        if ($vacancy->filled_count >= $vacancy->positions) {
            $vacancy->update(['status' => VacancyStatus::Filled]);
        }

        return $record->fresh(['employee', 'application']);
    }
}
