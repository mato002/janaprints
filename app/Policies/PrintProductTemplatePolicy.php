<?php

namespace App\Policies;

use App\Models\Production\PrintProductTemplate;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class PrintProductTemplatePolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('production.bom.view');
    }

    public function view(User $user, PrintProductTemplate $template): bool
    {
        return $user->can('production.bom.view') && $this->sameTenant($user, $template);
    }

    public function create(User $user): bool
    {
        return $user->can('production.bom.create');
    }

    public function update(User $user, PrintProductTemplate $template): bool
    {
        return $user->can('production.bom.edit') && $this->sameTenant($user, $template);
    }

    public function duplicate(User $user, PrintProductTemplate $template): bool
    {
        return $user->can('production.bom.create') && $this->sameTenant($user, $template);
    }
}
