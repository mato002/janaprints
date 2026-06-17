<?php

namespace App\Support\Hr;

use App\Enums\EmploymentStatus;
use App\Models\Employee;
use App\Models\Hr\EmployeeExit;
use App\Models\User;
use App\Services\Security\UserSessionService;
use Illuminate\Validation\ValidationException;

class EmployeeAccessGovernanceService
{
    public function __construct(
        protected UserSessionService $sessions,
        protected HrGovernanceAuditService $audit,
    ) {}

    public function onExitClosed(EmployeeExit $exit, User $actor): void
    {
        $employee = $exit->employee()->with('user')->first();

        if ($employee === null) {
            return;
        }

        $terminated = $this->lockEmployeeAccess(
            $employee,
            $actor,
            __('Employee exit closed (:reference).', ['reference' => $exit->reference]),
        );

        $this->audit->logEmployeeExitAccessLocked($exit, $actor, $terminated);
    }

    public function onSuspended(Employee $employee, User $actor, ?string $reason = null): void
    {
        $this->lockEmployeeAccess(
            $employee,
            $actor,
            $reason ?? __('Employee suspended.'),
        );

        $this->audit->logEmployeeSuspended($employee, $actor, $reason);
    }

    public function onReactivated(Employee $employee, User $actor, ?string $reason = null): void
    {
        $this->restoreEmployeeAccess($employee, $actor, $reason);

        $this->audit->logEmployeeReactivated($employee, $actor, $reason);
    }

    public function lockEmployeeAccess(Employee $employee, User $actor, string $reason): int
    {
        $employee->loadMissing('user');

        $employee->update([
            'is_active' => false,
        ]);

        $terminated = 0;
        $user = $employee->user;

        if ($user !== null) {
            $user->update(['is_active' => false]);
            $user->syncRoles([]);
            $this->audit->logRolesRevoked($user, $actor, $reason);
            $this->audit->logUserAccessDeactivated($user, $actor, $reason);
            $terminated = $this->sessions->forceLogoutUser($user, $actor, $reason);
        }

        return $terminated;
    }

    public function restoreEmployeeAccess(Employee $employee, User $actor, ?string $reason = null): void
    {
        $employee->loadMissing('user');

        if ($employee->employment_status === EmploymentStatus::Terminated) {
            throw ValidationException::withMessages([
                'employment_status' => __('Terminated employees cannot be reactivated from this action.'),
            ]);
        }

        $updates = ['is_active' => true];

        if ($employee->employment_status === EmploymentStatus::Suspended) {
            $updates['employment_status'] = EmploymentStatus::Active;
        }

        $employee->update($updates);

        $user = $employee->user;

        if ($user !== null) {
            $user->update(['is_active' => true]);
        }
    }

    public function canAuthenticate(User $user): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $this->employeeAllowsAccess($user->employee);
    }

    public function canResetPassword(User $user): bool
    {
        if (! $user->is_active) {
            return false;
        }

        return $this->employeeAllowsAccess($user->employee);
    }

    public function canCompleteActivation(Employee $employee): bool
    {
        return ! in_array($employee->employment_status, [
            EmploymentStatus::Suspended,
            EmploymentStatus::Terminated,
        ], true);
    }

    public function assertCanCompleteActivation(Employee $employee): void
    {
        if ($this->canCompleteActivation($employee)) {
            return;
        }

        throw ValidationException::withMessages([
            'token' => __('This employee account is not eligible for activation.'),
        ]);
    }

    protected function employeeAllowsAccess(?Employee $employee): bool
    {
        if ($employee === null) {
            return true;
        }

        if (in_array($employee->employment_status, [
            EmploymentStatus::Suspended,
            EmploymentStatus::Terminated,
        ], true)) {
            return false;
        }

        return $employee->is_active;
    }
}
