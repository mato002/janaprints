<?php

namespace App\Policies;

use App\Models\Communications\Inbox\CommunicationConversation;
use App\Models\User;

class CommunicationConversationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communications.inbox.view');
    }

    public function view(User $user, CommunicationConversation $conversation): bool
    {
        return $user->can('communications.inbox.view') && $this->sameCompany($user, $conversation->company_id);
    }

    public function reply(User $user): bool
    {
        return $user->can('communications.inbox.reply');
    }

    public function assign(User $user): bool
    {
        return $user->can('communications.inbox.assign');
    }

    public function close(User $user): bool
    {
        return $user->can('communications.inbox.close');
    }

    public function notes(User $user): bool
    {
        return $user->can('communications.inbox.notes');
    }

    public function attachments(User $user): bool
    {
        return $user->can('communications.inbox.attachments');
    }

    public function audit(User $user): bool
    {
        return $user->can('communications.inbox.audit');
    }

    public function escalate(User $user): bool
    {
        return $user->can('communications.inbox.escalate') || $user->can('communications.inbox.admin');
    }

    public function executive(User $user): bool
    {
        return $user->can('communications.inbox.executive') || $user->can('communications.inbox.admin');
    }

    public function admin(User $user): bool
    {
        return $user->can('communications.inbox.admin');
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        return $user->hasRole('Super Admin') || $user->company_id === $companyId;
    }
}
