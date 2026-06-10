<?php

namespace App\Policies\PrintingIntelligence;

use App\Models\PrintingIntelligence\PrintInkProfile;
use App\Models\User;

class PrintInkProfilePolicy
{
    use EnsuresCompanyTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('printing.ink-profiles.view');
    }

    public function view(User $user, PrintInkProfile $profile): bool
    {
        return $user->can('printing.ink-profiles.view') && $this->sameCompany($user, $profile);
    }

    public function create(User $user): bool
    {
        return $user->can('printing.ink-profiles.manage');
    }

    public function update(User $user, PrintInkProfile $profile): bool
    {
        return $user->can('printing.ink-profiles.manage') && $this->sameCompany($user, $profile);
    }

    public function delete(User $user, PrintInkProfile $profile): bool
    {
        return $user->can('printing.ink-profiles.manage') && $this->sameCompany($user, $profile);
    }
}
