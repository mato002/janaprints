<?php

namespace App\Support\Hr;

use App\Enums\JobRequisitionStatus;
use App\Enums\RecruitmentPipelineStage;
use App\Enums\VacancyStatus;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Hr\InterviewSchedule;
use App\Models\Hr\JobApplication;
use App\Models\Hr\JobRequisition;
use App\Models\Hr\OnboardingRecord;
use App\Models\Hr\Vacancy;
use App\Models\JobTitle;
use Illuminate\Support\Collection;

class RecruitmentDashboardService
{
    /**
     * @return array<string, int>
     */
    public function stats(int $companyId): array
    {
        return [
            'open_vacancies' => Vacancy::query()
                ->where('company_id', $companyId)
                ->where('status', VacancyStatus::Open->value)
                ->count(),
            'active_applications' => JobApplication::query()
                ->where('company_id', $companyId)
                ->whereNotIn('stage', [
                    RecruitmentPipelineStage::Rejected->value,
                    RecruitmentPipelineStage::Hired->value,
                ])
                ->count(),
            'upcoming_interviews' => InterviewSchedule::query()
                ->where('company_id', $companyId)
                ->where('scheduled_at', '>=', now())
                ->where('scheduled_at', '<=', now()->addDays(14))
                ->count(),
            'pending_onboarding' => OnboardingRecord::query()
                ->where('company_id', $companyId)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count(),
        ];
    }

    /**
     * @return Collection<int, JobApplication>
     */
    public function recentApplications(int $companyId, int $limit = 8): Collection
    {
        return JobApplication::query()
            ->where('company_id', $companyId)
            ->with(['candidate', 'vacancy'])
            ->orderByDesc('applied_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return array<string, int>
     */
    public function pipelineCounts(int $companyId): array
    {
        $counts = [];

        foreach (RecruitmentPipelineStage::cases() as $stage) {
            $counts[$stage->value] = JobApplication::query()
                ->where('company_id', $companyId)
                ->where('stage', $stage->value)
                ->count();
        }

        return $counts;
    }

    /**
     * @return array<string, mixed>
     */
    public function formData(int $companyId): array
    {
        return [
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'departments' => Department::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'jobTitles' => JobTitle::query()->where('company_id', $companyId)->orderBy('title')->get(),
            'employees' => Employee::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get(),
            'requisitions' => JobRequisition::query()
                ->where('company_id', $companyId)
                ->where('status', JobRequisitionStatus::Approved->value)
                ->orderBy('title')
                ->get(),
        ];
    }
}
