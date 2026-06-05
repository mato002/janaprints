<?php

namespace App\Support\Hr;

use App\Enums\SkillProficiency;
use App\Enums\TrainingAssignmentStatus;
use App\Models\Employee;
use App\Models\Hr\EmployeeSkill;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\TrainingProgram;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TrainingAssignmentService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = EmployeeTrainingAssignment::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['employee', 'program', 'assignedBy']);

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->whereHas('program', fn ($q) => $q->where('type', $filters['type']));
        }

        return $query->orderByDesc('assigned_at')->paginate($perPage)->withQueryString();
    }

    /**
     * @return array<string, mixed>
     */
    public function formData(int $companyId): array
    {
        return [
            'employees' => Employee::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->get(),
            'programs' => TrainingProgram::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('title')
                ->get(),
            'statuses' => TrainingAssignmentStatus::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(int $companyId): array
    {
        $base = EmployeeTrainingAssignment::query()
            ->forTenant()
            ->where('company_id', $companyId);

        return [
            'active_assignments' => (clone $base)->whereIn('status', [
                TrainingAssignmentStatus::Assigned->value,
                TrainingAssignmentStatus::InProgress->value,
            ])->count(),
            'completed_this_year' => (clone $base)
                ->where('status', TrainingAssignmentStatus::Completed->value)
                ->whereYear('completed_at', now()->year)
                ->count(),
            'total_hours' => round((float) (clone $base)
                ->where('status', TrainingAssignmentStatus::Completed->value)
                ->sum('hours_completed'), 1),
            'expiring_certificates' => (clone $base)->expiringCertificates(30)->count(),
        ];
    }

    /**
     * @return Collection<int, EmployeeTrainingAssignment>
     */
    public function expiringCertificates(int $companyId, int $days = 30): Collection
    {
        return EmployeeTrainingAssignment::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['employee', 'program'])
            ->expiringCertificates($days)
            ->orderBy('certificate_expires_at')
            ->limit(20)
            ->get();
    }

    /**
     * @return Collection<int, EmployeeSkill>
     */
    public function skillsMatrix(int $companyId, ?int $employeeId = null): Collection
    {
        $query = EmployeeSkill::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with('employee')
            ->orderBy('skill_name');

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        return $query->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function assign(int $companyId, array $data, User $user): EmployeeTrainingAssignment
    {
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->whereKey($data['employee_id'])
            ->firstOrFail();

        $program = TrainingProgram::query()
            ->where('company_id', $companyId)
            ->whereKey($data['training_program_id'])
            ->firstOrFail();

        return EmployeeTrainingAssignment::query()->create([
            'company_id' => $companyId,
            'employee_id' => $employee->id,
            'training_program_id' => $program->id,
            'reference' => $this->nextReference($companyId),
            'status' => TrainingAssignmentStatus::Assigned,
            'due_date' => $data['due_date'] ?? null,
            'assigned_at' => now(),
            'assigned_by_user_id' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function complete(EmployeeTrainingAssignment $assignment, array $data, User $user): EmployeeTrainingAssignment
    {
        if ($assignment->status === TrainingAssignmentStatus::Completed) {
            throw ValidationException::withMessages([
                'status' => __('This training is already completed.'),
            ]);
        }

        if ($assignment->status === TrainingAssignmentStatus::Cancelled) {
            throw ValidationException::withMessages([
                'status' => __('Cancelled training cannot be completed.'),
            ]);
        }

        return DB::transaction(function () use ($assignment, $data, $user) {
            $program = $assignment->program;
            $hours = $data['hours_completed'] ?? $program->duration_hours;
            $completedAt = now();
            $certificateRef = $data['certificate_reference'] ?? null;
            $expiresAt = null;

            if ($program->requires_certification) {
                $certificateRef ??= 'CERT-'.$assignment->reference;
                if ($program->certificate_validity_days) {
                    $expiresAt = $completedAt->copy()->addDays($program->certificate_validity_days)->toDateString();
                }
            }

            if (! empty($data['certificate_expires_at'])) {
                $expiresAt = $data['certificate_expires_at'];
            }

            $assignment->update([
                'status' => TrainingAssignmentStatus::Completed,
                'hours_completed' => $hours,
                'certificate_reference' => $certificateRef,
                'certificate_expires_at' => $expiresAt,
                'completed_at' => $completedAt,
                'completed_by_user_id' => $user->id,
                'notes' => trim(($assignment->notes ?? '')."\n".($data['notes'] ?? '')),
            ]);

            $this->syncSkillsFromProgram($assignment->fresh(['program']));

            return $assignment->fresh(['employee', 'program', 'skills']);
        });
    }

    protected function syncSkillsFromProgram(EmployeeTrainingAssignment $assignment): void
    {
        $tags = $assignment->program->skill_tags ?? [];

        foreach ($tags as $tag) {
            EmployeeSkill::query()->updateOrCreate(
                [
                    'employee_id' => $assignment->employee_id,
                    'skill_name' => $tag,
                ],
                [
                    'company_id' => $assignment->company_id,
                    'proficiency' => SkillProficiency::Intermediate,
                    'source_training_assignment_id' => $assignment->id,
                    'acquired_at' => $assignment->completed_at?->toDateString() ?? now()->toDateString(),
                ],
            );
        }
    }

    protected function nextReference(int $companyId): string
    {
        $year = now()->year;
        $count = EmployeeTrainingAssignment::query()
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('TRN-%s-%04d', $year, $count);
    }
}
