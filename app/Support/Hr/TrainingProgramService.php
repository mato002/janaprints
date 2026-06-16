<?php

namespace App\Support\Hr;

use App\Enums\TrainingAssignmentStatus;
use App\Enums\TrainingProgramStatus;
use App\Models\Hr\TrainingEvaluation;
use App\Models\Hr\TrainingProgram;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class TrainingProgramService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = TrainingProgram::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->withCount([
                'assignments',
                'assignments as completed_assignments_count' => fn ($q) => $q->where('status', TrainingAssignmentStatus::Completed->value),
            ]);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->orderBy('title')->paginate($perPage)->withQueryString();
    }

    public function findForShow(TrainingProgram $program): TrainingProgram
    {
        return $program->loadCount([
            'assignments',
            'assignments as completed_assignments_count' => fn ($q) => $q->where('status', TrainingAssignmentStatus::Completed->value),
            'assignments as in_progress_assignments_count' => fn ($q) => $q->where('status', TrainingAssignmentStatus::InProgress->value),
            'evaluations',
        ])->load([
            'evaluations' => fn ($q) => $q->with('evaluatedBy')->latest('evaluated_at')->limit(5),
        ]);
    }

    /**
     * @return array<string, int|float>
     */
    public function programStats(TrainingProgram $program): array
    {
        $total = $program->assignments_count ?? $program->assignments()->count();
        $completed = $program->completed_assignments_count
            ?? $program->assignments()->where('status', TrainingAssignmentStatus::Completed->value)->count();

        return [
            'assignments_count' => $total,
            'completed_count' => $completed,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0.0,
            'average_evaluation_score' => round((float) $program->evaluations()->avg('score'), 1),
            'evaluation_count' => $program->evaluations_count ?? $program->evaluations()->count(),
            'expiring_certificates_count' => $program->assignments()
                ->where('status', TrainingAssignmentStatus::Completed->value)
                ->whereNotNull('certificate_expires_at')
                ->where('certificate_expires_at', '>=', now()->toDateString())
                ->where('certificate_expires_at', '<=', now()->addDays(30)->toDateString())
                ->count(),
        ];
    }

    /**
     * @return array<string, int>
     */
    public function dashboardProgramStats(int $companyId): array
    {
        $base = TrainingProgram::query()->forTenant()->where('company_id', $companyId);

        return [
            'active_programs' => (clone $base)->active()->count(),
            'draft_programs' => (clone $base)->draft()->count(),
            'scheduled_programs' => (clone $base)->scheduled()->count(),
            'completed_programs' => (clone $base)->completed()->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data): TrainingProgram
    {
        $status = $this->resolveStatus($data['status'] ?? TrainingProgramStatus::Active->value);

        return TrainingProgram::query()->create([
            'company_id' => $companyId,
            'code' => $data['code'] ?? $this->nextCode($companyId),
            'type' => $data['type'],
            'status' => $status,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'duration_hours' => $data['duration_hours'] ?? 0,
            'budget_amount' => $data['budget_amount'] ?? null,
            'scheduled_start_date' => $data['scheduled_start_date'] ?? null,
            'scheduled_end_date' => $data['scheduled_end_date'] ?? null,
            'requires_certification' => (bool) ($data['requires_certification'] ?? false),
            'certificate_validity_days' => $data['certificate_validity_days'] ?? null,
            'skill_tags' => $this->parseSkillTags($data['skill_tags'] ?? null),
            'evaluation_instructions' => $data['evaluation_instructions'] ?? null,
            'is_active' => $status === TrainingProgramStatus::Active,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(TrainingProgram $program, array $data): TrainingProgram
    {
        $status = isset($data['status'])
            ? $this->resolveStatus($data['status'])
            : $program->status;

        $program->update([
            'type' => $data['type'] ?? $program->type,
            'status' => $status,
            'title' => $data['title'] ?? $program->title,
            'description' => $data['description'] ?? $program->description,
            'duration_hours' => $data['duration_hours'] ?? $program->duration_hours,
            'budget_amount' => array_key_exists('budget_amount', $data) ? $data['budget_amount'] : $program->budget_amount,
            'scheduled_start_date' => array_key_exists('scheduled_start_date', $data) ? $data['scheduled_start_date'] : $program->scheduled_start_date,
            'scheduled_end_date' => array_key_exists('scheduled_end_date', $data) ? $data['scheduled_end_date'] : $program->scheduled_end_date,
            'requires_certification' => array_key_exists('requires_certification', $data)
                ? (bool) $data['requires_certification']
                : $program->requires_certification,
            'certificate_validity_days' => array_key_exists('certificate_validity_days', $data)
                ? $data['certificate_validity_days']
                : $program->certificate_validity_days,
            'skill_tags' => array_key_exists('skill_tags', $data)
                ? $this->parseSkillTags($data['skill_tags'])
                : $program->skill_tags,
            'evaluation_instructions' => array_key_exists('evaluation_instructions', $data)
                ? $data['evaluation_instructions']
                : $program->evaluation_instructions,
            'is_active' => $status === TrainingProgramStatus::Active,
        ]);

        if (! $program->code) {
            $program->update(['code' => $this->nextCode($program->company_id)]);
        }

        return $program->fresh();
    }

    public function activate(TrainingProgram $program): TrainingProgram
    {
        if ($program->status !== TrainingProgramStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => __('Only draft programs can be activated.'),
            ]);
        }

        $program->update([
            'status' => TrainingProgramStatus::Active,
            'is_active' => true,
            'archived_at' => null,
        ]);

        return $program->fresh();
    }

    public function deactivate(TrainingProgram $program): TrainingProgram
    {
        if ($program->status !== TrainingProgramStatus::Active) {
            throw ValidationException::withMessages([
                'status' => __('Only active programs can be deactivated.'),
            ]);
        }

        $program->update([
            'status' => TrainingProgramStatus::Draft,
            'is_active' => false,
        ]);

        return $program->fresh();
    }

    public function complete(TrainingProgram $program): TrainingProgram
    {
        if ($program->status !== TrainingProgramStatus::Active) {
            throw ValidationException::withMessages([
                'status' => __('Only active programs can be marked completed.'),
            ]);
        }

        $program->update([
            'status' => TrainingProgramStatus::Completed,
            'is_active' => false,
        ]);

        return $program->fresh();
    }

    public function reopen(TrainingProgram $program): TrainingProgram
    {
        if (! in_array($program->status, [
            TrainingProgramStatus::Completed,
            TrainingProgramStatus::Archived,
            TrainingProgramStatus::Cancelled,
        ], true)) {
            throw ValidationException::withMessages([
                'status' => __('This program cannot be reopened.'),
            ]);
        }

        $program->update([
            'status' => TrainingProgramStatus::Active,
            'is_active' => true,
            'archived_at' => null,
        ]);

        return $program->fresh();
    }

    public function archive(TrainingProgram $program): TrainingProgram
    {
        if ($program->status === TrainingProgramStatus::Archived) {
            throw ValidationException::withMessages([
                'status' => __('This program is already archived.'),
            ]);
        }

        $program->update([
            'status' => TrainingProgramStatus::Archived,
            'is_active' => false,
            'archived_at' => now(),
        ]);

        return $program->fresh();
    }

    public function duplicate(TrainingProgram $program): TrainingProgram
    {
        $copy = $program->replicate([
            'code',
            'status',
            'is_active',
            'archived_at',
            'created_at',
            'updated_at',
        ]);

        $copy->fill([
            'code' => $this->nextCode($program->company_id),
            'status' => TrainingProgramStatus::Draft,
            'is_active' => false,
            'archived_at' => null,
            'duplicated_from_id' => $program->id,
            'title' => $program->title.' ('.__('Copy').')',
        ]);

        $copy->save();

        return $copy->fresh();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordEvaluation(TrainingProgram $program, array $data, User $user): TrainingEvaluation
    {
        return TrainingEvaluation::query()->create([
            'company_id' => $program->company_id,
            'training_program_id' => $program->id,
            'employee_training_assignment_id' => $data['employee_training_assignment_id'] ?? null,
            'score' => $data['score'],
            'feedback' => $data['feedback'] ?? null,
            'evaluated_by_user_id' => $user->id,
            'evaluated_at' => now(),
        ]);
    }

    /**
     * @return Collection<int, TrainingProgram>
     */
    public function calendar(int $companyId, int $year, int $month, array $filters = []): Collection
    {
        $start = now()->setDate($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $query = TrainingProgram::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->whereNotNull('scheduled_start_date')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_start_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhereBetween('scheduled_end_date', [$start->toDateString(), $end->toDateString()])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('scheduled_start_date', '<=', $start->toDateString())
                            ->where('scheduled_end_date', '>=', $end->toDateString());
                    });
            });

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('scheduled_start_date')->get();
    }

    /**
     * @return Collection<int, TrainingProgram>
     */
    public function upcomingScheduled(int $companyId, int $limit = 8): Collection
    {
        return TrainingProgram::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->scheduled()
            ->where('scheduled_start_date', '>=', now()->toDateString())
            ->orderBy('scheduled_start_date')
            ->limit($limit)
            ->get();
    }

    public function nextCode(int $companyId): string
    {
        $year = now()->year;
        $count = TrainingProgram::query()
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('TRP-%s-%04d', $year, $count);
    }

    protected function resolveStatus(string $status): TrainingProgramStatus
    {
        return TrainingProgramStatus::from($status);
    }

    /**
     * @return list<string>
     */
    protected function parseSkillTags(mixed $tags): array
    {
        if (is_array($tags)) {
            return array_values(array_filter(array_map('trim', $tags)));
        }

        if (! $tags) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $tags))));
    }
}
