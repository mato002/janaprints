<?php

namespace App\Policies;

use App\Models\Commercial\CommercialComplaint;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class CommercialComplaintPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('commercial.complaints.view');
    }

    public function view(User $user, CommercialComplaint $complaint): bool
    {
        return $user->can('commercial.complaints.view') && $this->sameTenant($user, $complaint);
    }

    public function create(User $user): bool
    {
        return $user->can('commercial.complaints.create');
    }

    public function update(User $user, CommercialComplaint $complaint): bool
    {
        return $user->can('commercial.complaints.edit') && $this->sameTenant($user, $complaint);
    }

    public function resolve(User $user, CommercialComplaint $complaint): bool
    {
        return $user->can('commercial.complaints.resolve') && $this->sameTenant($user, $complaint);
    }
}
