<?php

namespace App\Services\EmailIdentity;

use App\Models\Employee;
use App\Models\User;
use Spatie\Permission\Models\Role;

class EmployeeActivationRoleResolver
{
    public function __construct(
        protected EmployeeDefaultRoleService $defaultRoles,
    ) {}

    public function resolve(Employee $employee, ?string $explicitRole = null): ?string
    {
        $employee->loadMissing(['jobTitle', 'department']);

        if ($explicitRole && $this->roleExists($explicitRole) && $this->canAssignRoleAtActivation($employee, $explicitRole)) {
            return $explicitRole;
        }

        $jobTitleCode = $employee->jobTitle?->code;
        if ($jobTitleCode) {
            $mapped = config("employee_onboarding.job_title_role_map.{$jobTitleCode}");
            if ($this->isValidMappedRole($mapped, $employee)) {
                return $mapped;
            }
        }

        $departmentCode = $employee->department?->code;
        if ($departmentCode) {
            $mapped = config("employee_onboarding.department_role_map.{$departmentCode}");
            if ($this->isValidMappedRole($mapped, $employee)) {
                return $mapped;
            }
        }

        $resolved = $this->defaultRoles->resolveDefaultRole();
        if ($this->isValidMappedRole($resolved, $employee)) {
            return $resolved;
        }

        return null;
    }

    public function roleExists(?string $roleName): bool
    {
        if (! filled($roleName)) {
            return false;
        }

        return Role::query()
            ->where('guard_name', 'web')
            ->where('name', $roleName)
            ->exists();
    }

    public function isAssignable(string $roleName, ?User $assigner = null): bool
    {
        if ($roleName === 'Super Admin') {
            $assigner ??= auth()->user();

            return $assigner?->hasRole('Super Admin') ?? false;
        }

        return $this->roleExists($roleName);
    }

    /**
     * Roles chosen by an admin during employee onboarding must still apply at
     * activation when the new user is unauthenticated (e.g. Super Admin).
     */
    public function canAssignRoleAtActivation(Employee $employee, string $roleName): bool
    {
        if ($this->isAssignable($roleName)) {
            return true;
        }

        // System role pre-selected by an admin during employee onboarding.
        if (filled($employee->activation_role) && $employee->activation_role === $roleName) {
            return true;
        }

        return false;
    }

    /**
     * @return \Illuminate\Support\Collection<int, Role>
     */
    public function assignableRolesFor(?User $user = null): \Illuminate\Support\Collection
    {
        $user ??= auth()->user();

        $query = Role::query()->where('guard_name', 'web')->orderBy('name');

        if (! $user?->hasRole('Super Admin')) {
            $query->where('name', '!=', 'Super Admin');
        }

        return $query->get();
    }

    protected function isValidMappedRole(?string $roleName, ?Employee $employee = null): bool
    {
        if (! $this->roleExists($roleName)) {
            return false;
        }

        return $employee
            ? $this->canAssignRoleAtActivation($employee, $roleName)
            : $this->isAssignable($roleName);
    }
}
