<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebsiteMediaItem;

class WebsiteMediaItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('website.media.view');
    }

    public function view(User $user, WebsiteMediaItem $item): bool
    {
        return $user->can('website.media.view');
    }

    public function update(User $user, WebsiteMediaItem $item): bool
    {
        return $user->can('website.media.edit');
    }
}
