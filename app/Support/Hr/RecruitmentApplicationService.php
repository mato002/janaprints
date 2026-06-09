<?php

namespace App\Support\Hr;

use App\Enums\InterviewRecommendation;
use App\Enums\InterviewScheduleStatus;
use App\Enums\OfferLetterStatus;
use App\Enums\RecruitmentPipelineStage;
use App\Enums\VacancyStatus;
use App\Models\Hr\Candidate;
use App\Models\Hr\InterviewFeedback;
use App\Models\Hr\InterviewSchedule;
use App\Models\Hr\JobApplication;
use App\Models\Hr\OfferLetter;
use App\Models\Hr\Vacancy;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class RecruitmentApplicationService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = JobApplication::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['candidate', 'vacancy']);

        if (! empty($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }

        if (! empty($filters['vacancy_id'])) {
            $query->where('vacancy_id', $filters['vacancy_id']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('reference', 'like', '%'.$filters['search'].'%')
                    ->orWhereHas('candidate', function ($cq) use ($filters) {
                        $cq->where('first_name', 'like', '%'.$filters['search'].'%')
                            ->orWhere('last_name', 'like', '%'.$filters['search'].'%')
                            ->orWhere('email', 'like', '%'.$filters['search'].'%');
                    });
            });
        }

        return $query->orderByDesc('applied_at')->paginate($perPage)->withQueryString();
    }

    /**
     * @return Collection<string, Collection<int, JobApplication>>
     */
    public function pipelineBoard(int $companyId): Collection
    {
        $applications = JobApplication::query()
            ->where('company_id', $companyId)
            ->with(['candidate', 'vacancy'])
            ->orderByDesc('applied_at')
            ->get();

        return $applications->groupBy(fn (JobApplication $app) => $app->stage->value);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function apply(int $companyId, array $data, User $user): JobApplication
    {
        $vacancy = Vacancy::query()
            ->where('company_id', $companyId)
            ->whereKey($data['vacancy_id'])
            ->firstOrFail();

        if ($vacancy->status !== VacancyStatus::Open) {
            throw ValidationException::withMessages([
                'vacancy_id' => __('Applications are only accepted for open vacancies.'),
            ]);
        }

        $candidate = $this->resolveCandidate($companyId, $data);

        $existing = JobApplication::query()
            ->where('vacancy_id', $vacancy->id)
            ->where('candidate_id', $candidate->id)
            ->exists();

        if ($existing) {
            throw ValidationException::withMessages([
                'candidate' => __('This candidate has already applied for this vacancy.'),
            ]);
        }

        return JobApplication::query()->create([
            'company_id' => $companyId,
            'vacancy_id' => $vacancy->id,
            'candidate_id' => $candidate->id,
            'reference' => $this->nextReference($companyId),
            'stage' => RecruitmentPipelineStage::Applied,
            'applied_at' => now(),
            'notes' => $data['notes'] ?? null,
            'created_by_user_id' => $user->id,
        ]);
    }

    public function advanceStage(JobApplication $application, RecruitmentPipelineStage $stage): JobApplication
    {
        if (in_array($application->stage, [RecruitmentPipelineStage::Rejected, RecruitmentPipelineStage::Hired], true)) {
            throw ValidationException::withMessages([
                'stage' => __('Closed applications cannot be moved.'),
            ]);
        }

        $application->update(['stage' => $stage]);

        return $application->fresh();
    }

    public function reject(JobApplication $application): JobApplication
    {
        $application->update(['stage' => RecruitmentPipelineStage::Rejected]);

        return $application->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function scheduleInterview(JobApplication $application, array $data, User $user): InterviewSchedule
    {
        $schedule = InterviewSchedule::query()->create([
            'company_id' => $application->company_id,
            'job_application_id' => $application->id,
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => (int) ($data['duration_minutes'] ?? 60),
            'location' => $data['location'] ?? null,
            'meeting_link' => $data['meeting_link'] ?? null,
            'interviewer_user_id' => $data['interviewer_user_id'] ?? $user->id,
            'status' => InterviewScheduleStatus::Scheduled,
            'notes' => $data['notes'] ?? null,
            'created_by_user_id' => $user->id,
        ]);

        if ($application->stage === RecruitmentPipelineStage::Applied
            || $application->stage === RecruitmentPipelineStage::Screening) {
            $application->update(['stage' => RecruitmentPipelineStage::Interview]);
        }

        return $schedule;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordFeedback(InterviewSchedule $schedule, array $data, User $user): InterviewFeedback
    {
        $feedback = InterviewFeedback::query()->updateOrCreate(
            ['interview_schedule_id' => $schedule->id],
            [
                'company_id' => $schedule->company_id,
                'rating' => min(5, max(1, (int) ($data['rating'] ?? 3))),
                'recommendation' => $data['recommendation'] ?? InterviewRecommendation::Hold->value,
                'feedback' => $data['feedback'] ?? null,
                'submitted_by_user_id' => $user->id,
                'submitted_at' => now(),
            ],
        );

        $schedule->update(['status' => InterviewScheduleStatus::Completed]);

        if ($feedback->recommendation === InterviewRecommendation::Hire) {
            $schedule->application->update(['stage' => RecruitmentPipelineStage::Shortlisted]);
        } elseif ($feedback->recommendation === InterviewRecommendation::Reject) {
            $schedule->application->update(['stage' => RecruitmentPipelineStage::Rejected]);
        }

        return $feedback;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createOffer(JobApplication $application, array $data, User $user): OfferLetter
    {
        $offer = OfferLetter::query()->create([
            'company_id' => $application->company_id,
            'job_application_id' => $application->id,
            'reference' => $this->nextOfferReference($application->company_id),
            'salary_offered' => $data['salary_offered'] ?? null,
            'start_date' => $data['start_date'] ?? null,
            'terms' => $data['terms'] ?? null,
            'status' => OfferLetterStatus::Draft,
            'created_by_user_id' => $user->id,
        ]);

        $application->update(['stage' => RecruitmentPipelineStage::Offer]);

        return $offer;
    }

    public function sendOffer(OfferLetter $offer): OfferLetter
    {
        if ($offer->status !== OfferLetterStatus::Draft) {
            throw ValidationException::withMessages(['status' => __('Only draft offers can be sent.')]);
        }

        $offer->update([
            'status' => OfferLetterStatus::Sent,
            'sent_at' => now(),
        ]);

        return $offer->fresh();
    }

    public function acceptOffer(OfferLetter $offer): OfferLetter
    {
        if ($offer->status !== OfferLetterStatus::Sent) {
            throw ValidationException::withMessages(['status' => __('Only sent offers can be accepted.')]);
        }

        $offer->update([
            'status' => OfferLetterStatus::Accepted,
            'responded_at' => now(),
        ]);

        $offer->application->update(['stage' => RecruitmentPipelineStage::Accepted]);

        return $offer->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveCandidate(int $companyId, array $data): Candidate
    {
        if (! empty($data['candidate_id'])) {
            return Candidate::query()
                ->where('company_id', $companyId)
                ->whereKey($data['candidate_id'])
                ->firstOrFail();
        }

        return Candidate::query()->create([
            'company_id' => $companyId,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'resume_notes' => $data['resume_notes'] ?? null,
            'source' => $data['source'] ?? null,
        ]);
    }

    protected function nextReference(int $companyId): string
    {
        $count = JobApplication::query()->where('company_id', $companyId)->count() + 1;

        return sprintf('APP-%04d', $count);
    }

    protected function nextOfferReference(int $companyId): string
    {
        $count = OfferLetter::query()->where('company_id', $companyId)->count() + 1;

        return sprintf('OFF-%04d', $count);
    }
}
