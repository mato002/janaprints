<?php

namespace App\Policies;

use App\Models\Communications\CommunicationTemplate;
use App\Models\User;

class CommunicationTemplatePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('communications.templates.view');
    }

    public function view(User $user, CommunicationTemplate $template): bool
    {
        return $user->can('communications.templates.view') && $this->sameCompany($user, $template->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('communications.templates.create');
    }

    public function update(User $user, CommunicationTemplate $template): bool
    {
        return $user->can('communications.templates.edit') && $this->sameCompany($user, $template->company_id);
    }

    public function viewVersions(User $user, CommunicationTemplate $template): bool
    {
        return $user->can('communications.templates.version_view') && $this->sameCompany($user, $template->company_id);
    }

    public function restore(User $user, CommunicationTemplate $template): bool
    {
        return $user->can('communications.templates.restore') && $this->sameCompany($user, $template->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}
