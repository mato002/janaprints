<?php

namespace App\Policies;

use App\Models\Commercial\CommercialSupportTicket;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class CommercialSupportTicketPolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('commercial.tickets.view');
    }

    public function view(User $user, CommercialSupportTicket $ticket): bool
    {
        return $user->can('commercial.tickets.view') && $this->sameTenant($user, $ticket);
    }

    public function create(User $user): bool
    {
        return $user->can('commercial.tickets.create');
    }

    public function update(User $user, CommercialSupportTicket $ticket): bool
    {
        return $user->can('commercial.tickets.edit') && $this->sameTenant($user, $ticket);
    }

    public function assign(User $user, CommercialSupportTicket $ticket): bool
    {
        return $user->can('commercial.tickets.assign') && $this->sameTenant($user, $ticket);
    }

    public function resolve(User $user, CommercialSupportTicket $ticket): bool
    {
        return $user->can('commercial.tickets.resolve') && $this->sameTenant($user, $ticket);
    }
}
