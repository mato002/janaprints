<?php

namespace App\Policies;

use App\Models\User;

class BackupManagementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('operations.backups.view');
    }

    public function view(User $user): bool
    {
        return $user->can('operations.backups.view');
    }

    public function download(User $user): bool
    {
        return $user->can('operations.backups.download');
    }

    public function manage(User $user): bool
    {
        return $user->can('operations.backups.manage');
    }
}
