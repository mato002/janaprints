<?php

namespace App\Policies;

use App\Models\User;

class AuditLogsPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('operations.audit.view');
    }

    public function view(User $user): bool
    {
        return $user->can('operations.audit.view');
    }

    public function export(User $user): bool
    {
        return $user->can('operations.audit.export');
    }
}
