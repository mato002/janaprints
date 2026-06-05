<?php

namespace App\Policies;

use App\Models\PublicContactMessage;
use App\Models\User;

class PublicContactMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('public_leads.contact_messages.view');
    }

    public function view(User $user, PublicContactMessage $contactMessage): bool
    {
        return $user->can('public_leads.contact_messages.view');
    }

    public function update(User $user, PublicContactMessage $contactMessage): bool
    {
        return $user->can('public_leads.contact_messages.manage');
    }
}
