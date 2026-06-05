<?php

namespace App\Support\Hr;

use App\Enums\ClearanceCategory;
use App\Enums\ClearanceStatus;
use App\Enums\EmploymentStatus;
use App\Enums\ExitStatus;
use App\Models\Employee;
use App\Models\Hr\EmployeeExit;
use App\Models\Hr\EmployeeExitClearance;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeExitService
{
    public function __construct(
        protected ExitFinalDuesService $finalDues,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = EmployeeExit::query()
            ->forTenant()
            ->where('company_id', $companyId)
            ->with(['employee', 'initiatedBy']);

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        if (! empty($filters['exit_type'])) {
            $query->where('exit_type', $filters['exit_type']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('initiated_at')->paginate($perPage)->withQueryString();
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
            'exitTypes' => \App\Enums\ExitType::cases(),
            'statuses' => ExitStatus::cases(),
            'clearanceCategories' => ClearanceCategory::cases(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardStats(int $companyId): array
    {
        $base = EmployeeExit::query()->forTenant()->where('company_id', $companyId);

        return [
            'active_exits' => (clone $base)->whereNotIn('status', [ExitStatus::Closed->value])->count(),
            'pending_clearance' => (clone $base)->whereIn('status', [
                ExitStatus::Initiated->value,
                ExitStatus::ClearanceInProgress->value,
            ])->count(),
            'settled_this_year' => (clone $base)->where('status', ExitStatus::Settled->value)->whereYear('settled_at', now()->year)->count(),
            'closed_this_year' => (clone $base)->where('status', ExitStatus::Closed->value)->whereYear('closed_at', now()->year)->count(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function initiate(int $companyId, array $data, User $user): EmployeeExit
    {
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereKey($data['employee_id'])
            ->firstOrFail();

        if (EmployeeExit::query()
            ->where('employee_id', $employee->id)
            ->whereNotIn('status', [ExitStatus::Closed->value])
            ->exists()) {
            throw ValidationException::withMessages([
                'employee_id' => __('This employee already has an active exit process.'),
            ]);
        }

        $lastWorkingDate = Carbon::parse($data['last_working_date']);
        $dues = $this->finalDues->calculate($employee, $lastWorkingDate);

        return DB::transaction(function () use ($companyId, $data, $user, $employee, $lastWorkingDate, $dues) {
            $exit = EmployeeExit::query()->create([
                'company_id' => $companyId,
                'branch_id' => $employee->branch_id,
                'employee_id' => $employee->id,
                'reference' => $this->nextReference($companyId),
                'exit_type' => $data['exit_type'],
                'status' => ExitStatus::Initiated,
                'last_working_date' => $lastWorkingDate,
                'exit_date' => $data['exit_date'] ?? $lastWorkingDate,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                ...$dues,
                'initiated_by_user_id' => $user->id,
                'initiated_at' => now(),
            ]);

            foreach (ClearanceCategory::cases() as $category) {
                EmployeeExitClearance::query()->create([
                    'employee_exit_id' => $exit->id,
                    'category' => $category,
                    'status' => ClearanceStatus::Pending,
                ]);
            }

            return $exit->fresh(['employee', 'clearances']);
        });
    }

    public function updateClearance(
        EmployeeExitClearance $clearance,
        ClearanceStatus $status,
        User $user,
        ?string $notes = null,
    ): EmployeeExit {
        if ($clearance->exit->status === ExitStatus::Closed) {
            throw ValidationException::withMessages([
                'status' => __('This exit process is already closed.'),
            ]);
        }

        return DB::transaction(function () use ($clearance, $status, $user, $notes) {
            $clearance->update([
                'status' => $status,
                'cleared_by_user_id' => $user->id,
                'cleared_at' => now(),
                'notes' => $notes,
            ]);

            $exit = $clearance->exit->fresh(['clearances']);

            if ($exit->status === ExitStatus::Initiated) {
                $exit->update(['status' => ExitStatus::ClearanceInProgress]);
            }

            if ($exit->isClearanceComplete()) {
                $exit->update(['status' => ExitStatus::ClearanceComplete]);
            }

            return $exit->fresh(['employee', 'clearances.clearedBy']);
        });
    }

    public function settle(EmployeeExit $exit, User $user): EmployeeExit
    {
        if ($exit->status !== ExitStatus::ClearanceComplete) {
            throw ValidationException::withMessages([
                'status' => __('Complete clearance before settling final dues.'),
            ]);
        }

        $dues = $this->finalDues->calculate(
            $exit->employee,
            Carbon::parse($exit->last_working_date),
        );

        $exit->update([
            'status' => ExitStatus::Settled,
            ...$dues,
            'settled_by_user_id' => $user->id,
            'settled_at' => now(),
        ]);

        return $exit->fresh(['employee', 'clearances']);
    }

    public function close(EmployeeExit $exit, User $user): EmployeeExit
    {
        if ($exit->status !== ExitStatus::Settled) {
            throw ValidationException::withMessages([
                'status' => __('Exit must be settled before closing.'),
            ]);
        }

        return DB::transaction(function () use ($exit, $user) {
            $exit->update([
                'status' => ExitStatus::Closed,
                'closed_by_user_id' => $user->id,
                'closed_at' => now(),
            ]);

            $exit->employee->update([
                'employment_status' => EmploymentStatus::Terminated,
                'is_active' => false,
            ]);

            return $exit->fresh(['employee', 'clearances']);
        });
    }

    protected function nextReference(int $companyId): string
    {
        $year = now()->year;
        $count = EmployeeExit::query()
            ->where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return sprintf('EXIT-%s-%04d', $year, $count);
    }
}
