<?php

namespace App\Policies;

use App\Models\SecurityAuditEvent;
use App\Models\User;

class SecurityAuditEventPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('security.audit.view');
    }

    public function view(User $user, SecurityAuditEvent $event): bool
    {
        return $user->can('security.audit.view');
    }

    public function export(User $user): bool
    {
        return $user->can('security.audit.export');
    }

    public function manage(User $user): bool
    {
        return $user->can('security.audit.manage');
    }
}
