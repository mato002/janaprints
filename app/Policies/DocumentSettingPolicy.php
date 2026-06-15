<?php

namespace App\Policies;

use App\Models\DocumentSetting;
use App\Models\User;

class DocumentSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('documents.settings.view');
    }

    public function update(User $user): bool
    {
        return $user->can('documents.settings.edit');
    }
}
