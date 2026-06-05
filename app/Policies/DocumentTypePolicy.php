<?php

namespace App\Policies;

use App\Models\Platform\DocumentTypeDefinition;
use App\Models\User;

class DocumentTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('configuration.document_types.view');
    }

    public function view(User $user, DocumentTypeDefinition $documentType): bool
    {
        return $user->can('configuration.document_types.view');
    }

    public function create(User $user): bool
    {
        return $user->can('configuration.document_types.create');
    }

    public function update(User $user, DocumentTypeDefinition $documentType): bool
    {
        return $user->can('configuration.document_types.edit');
    }

    public function activate(User $user, DocumentTypeDefinition $documentType): bool
    {
        return $user->can('configuration.document_types.activate');
    }

    public function deactivate(User $user, DocumentTypeDefinition $documentType): bool
    {
        return $user->can('configuration.document_types.deactivate');
    }
}
