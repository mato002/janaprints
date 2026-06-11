<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebsiteSetting;

class WebsiteSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('website.settings.view');
    }

    public function update(User $user): bool
    {
        return $user->can('website.settings.edit');
    }
}
