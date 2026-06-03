<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view') && $this->sameTenant($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->can('users.create');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.edit') && $this->sameTenant($user, $model);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('users.delete') && $this->sameTenant($user, $model) && $user->id !== $model->id;
    }

    public function resetPassword(User $user, User $model): bool
    {
        return $user->can('users.edit') && $this->sameTenant($user, $model);
    }

    public function toggleActive(User $user, User $model): bool
    {
        return $user->can('users.edit') && $this->sameTenant($user, $model) && $user->id !== $model->id;
    }

    protected function sameTenant(User $actor, User $target): bool
    {
        if ($actor->hasRole('Super Admin')) {
            return true;
        }

        return $actor->company_id !== null
            && $actor->company_id === $target->company_id;
    }
}
