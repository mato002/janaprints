<?php

namespace App\Policies;

use App\Models\Hr\EmployeeDocument;
use App\Models\User;

class EmployeeDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('hr.documents.view');
    }

    public function view(User $user, EmployeeDocument $document): bool
    {
        return $user->can('hr.documents.view') && $this->sameCompany($user, $document->company_id);
    }

    public function create(User $user): bool
    {
        return $user->can('hr.documents.upload');
    }

    public function upload(User $user, EmployeeDocument $document): bool
    {
        return $user->can('hr.documents.upload') && $this->sameCompany($user, $document->company_id);
    }

    public function delete(User $user, EmployeeDocument $document): bool
    {
        return $user->can('hr.documents.delete') && $this->sameCompany($user, $document->company_id);
    }

    protected function sameCompany(User $user, int $companyId): bool
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }

        return $user->company_id === $companyId;
    }
}
