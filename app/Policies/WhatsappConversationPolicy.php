<?php

namespace App\Policies;

use App\Models\Communications\WhatsappConversation;
use App\Models\User;

class WhatsappConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communications.whatsapp.view');
    }

    public function view(User $user, WhatsappConversation $conversation): bool
    {
        return $user->can('communications.whatsapp.view') && $this->sameCompany($user, $conversation->company_id);
    }

    public function send(User $user): bool
    {
        return $user->can('communications.whatsapp.send');
    }

    public function manage(User $user): bool
    {
        return $user->can('communications.whatsapp.manage');
    }

    public function audit(User $user): bool
    {
        return $user->can('communications.whatsapp.audit');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $companyId;
    }
}
