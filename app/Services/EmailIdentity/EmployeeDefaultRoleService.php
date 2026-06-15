<?php

namespace App\Services\EmailIdentity;

use Spatie\Permission\Models\Role;

class EmployeeDefaultRoleService
{
    /**
     * @return list<string>
     */
    public function fallbackRoles(): array
    {
        $roles = config('employee_onboarding.fallback_roles', ['Staff', 'Viewer']);

        if (! is_array($roles) || $roles === []) {
            $legacy = config('employee_onboarding.default_role');
            if (filled($legacy)) {
                return [(string) $legacy];
            }

            return ['Staff', 'Viewer'];
        }

        return array_values(array_filter(array_map('strval', $roles)));
    }

    public function resolveDefaultRole(): ?string
    {
        foreach ($this->fallbackRoles() as $roleName) {
            if ($this->roleExists($roleName)) {
                return $roleName;
            }
        }

        return null;
    }

    public function staffRoleExists(): bool
    {
        return $this->roleExists('Staff');
    }

    public function staffRoleMissing(): bool
    {
        return ! $this->staffRoleExists();
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
}
