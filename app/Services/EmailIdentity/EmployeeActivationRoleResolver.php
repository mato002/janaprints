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

        if ($explicitRole && $this->roleExists($explicitRole) && $this->isAssignable($explicitRole)) {
            return $explicitRole;
        }

        $jobTitleCode = $employee->jobTitle?->code;
        if ($jobTitleCode) {
            $mapped = config("employee_onboarding.job_title_role_map.{$jobTitleCode}");
            if ($this->isValidMappedRole($mapped)) {
                return $mapped;
            }
        }

        $departmentCode = $employee->department?->code;
        if ($departmentCode) {
            $mapped = config("employee_onboarding.department_role_map.{$departmentCode}");
            if ($this->isValidMappedRole($mapped)) {
                return $mapped;
            }
        }

        $resolved = $this->defaultRoles->resolveDefaultRole();
        if ($this->isValidMappedRole($resolved)) {
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

    protected function isValidMappedRole(?string $roleName): bool
    {
        return $this->roleExists($roleName) && $this->isAssignable($roleName);
    }
}
