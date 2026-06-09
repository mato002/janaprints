<?php

namespace App\Support\Security;

use App\Models\User;
use Illuminate\Support\Collection;

class UserRoleAssignmentService
{
    /**
     * @param  list<string>  $roles
     */
    public function sync(User $user, array $roles, string $primaryRole): void
    {
        $roles = $this->normalizeRoles($roles);
        $primaryRole = trim($primaryRole);

        if ($primaryRole === '' || ! in_array($primaryRole, $roles, true)) {
            $primaryRole = $roles[0];
        }

        $user->syncRoles($roles);
        $user->update(['primary_role' => $primaryRole]);
    }

    public function primaryRoleName(User $user): ?string
    {
        if ($user->primary_role) {
            return $user->primary_role;
        }

        return $user->getRoleNames()->first();
    }

    /**
     * @return list<string>
     */
    public function secondaryRoleNames(User $user): array
    {
        $primary = $this->primaryRoleName($user);

        return $user->getRoleNames()
            ->reject(fn (string $name) => $name === $primary)
            ->values()
            ->all();
    }

    public function summaryLabel(User $user): string
    {
        $primary = $this->primaryRoleName($user);

        if ($primary === null) {
            return '—';
        }

        $secondaries = $this->secondaryRoleNames($user);

        if ($secondaries === []) {
            return $primary;
        }

        return $primary.' (+ '.implode(', ', $secondaries).')';
    }

    /**
     * @return array{roles: list<string>, primary_role: ?string}
     */
    public function snapshot(User $user): array
    {
        return [
            'roles' => $user->getRoleNames()->sort()->values()->all(),
            'primary_role' => $this->primaryRoleName($user),
        ];
    }

    /**
     * @param  list<string>  $roles
     * @return list<string>
     */
    protected function normalizeRoles(array $roles): array
    {
        return Collection::make($roles)
            ->map(fn ($role) => trim((string) $role))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
