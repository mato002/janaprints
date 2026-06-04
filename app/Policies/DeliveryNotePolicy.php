<?php

namespace App\Policies;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class DeliveryNotePolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('dispatch.view');
    }

    public function view(User $user, DeliveryNote $note): bool
    {
        return $user->can('dispatch.view') && $this->sameTenant($user, $note);
    }

    public function create(User $user): bool
    {
        return $user->can('dispatch.create');
    }

    public function update(User $user, DeliveryNote $note): bool
    {
        return $user->can('dispatch.create')
            && $this->sameTenant($user, $note)
            && $note->status->isEditable();
    }

    public function dispatch(User $user, DeliveryNote $note): bool
    {
        return $user->can('dispatch.dispatch')
            && $this->sameTenant($user, $note)
            && $note->status->canDispatch();
    }

    public function deliver(User $user, DeliveryNote $note): bool
    {
        return $user->can('dispatch.deliver')
            && $this->sameTenant($user, $note)
            && $note->status->canDeliver();
    }

    public function cancel(User $user, DeliveryNote $note): bool
    {
        return $user->can('dispatch.cancel')
            && $this->sameTenant($user, $note)
            && $note->status->canCancel()
            && $note->status !== DeliveryNoteStatus::Delivered;
    }
}
