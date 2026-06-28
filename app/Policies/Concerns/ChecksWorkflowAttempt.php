<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksWorkflowAttempt
{
    /**
     * Whether the user may see and attempt a workflow action.
     * Business rules are enforced on submit; hide only when the action is finished or unavailable.
     *
     * @param  callable(object): bool|null  $hideWhen
     */
    protected function canAttemptWorkflow(User $user, object $model, string $permission, ?callable $hideWhen = null): bool
    {
        if (! $user->can($permission) || ! $this->sameTenant($user, $model)) {
            return false;
        }

        return $hideWhen === null || ! $hideWhen($model);
    }
}
