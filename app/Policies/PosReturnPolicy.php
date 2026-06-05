<?php

namespace App\Policies;

use App\Models\Pos\PosReturn;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class PosReturnPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('commercial.pos.returns.view');
    }

    public function view(User $user, PosReturn $return): bool
    {
        return $user->can('commercial.pos.returns.view') && $this->sameTenant($user, $return);
    }

    public function create(User $user): bool
    {
        return $user->can('commercial.pos.returns.create');
    }

    public function approve(User $user, PosReturn $return): bool
    {
        return $user->can('commercial.pos.returns.approve') && $this->sameTenant($user, $return);
    }

    public function reject(User $user, PosReturn $return): bool
    {
        return $user->can('commercial.pos.returns.approve') && $this->sameTenant($user, $return);
    }

    public function audit(User $user, PosReturn $return): bool
    {
        return $user->can('commercial.pos.returns.audit') && $this->view($user, $return);
    }
}
