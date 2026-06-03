<?php

namespace App\Policies;

use App\Enums\InventoryDocumentStatus;
use App\Enums\StockIssueDestination;
use App\Models\Inventory\StockIssue;
use App\Models\User;
use App\Policies\Concerns\ChecksCrmTenant;

class StockIssuePolicy
{
    use ChecksCrmTenant;

    public function viewAny(User $user): bool
    {
        return $user->can('inventory.view');
    }

    public function view(User $user, StockIssue $issue): bool
    {
        return $user->can('inventory.view') && $this->sameTenant($user, $issue);
    }

    public function create(User $user): bool
    {
        return $user->can('inventory.issue') || $user->can('inventory.transfer');
    }

    public function update(User $user, StockIssue $issue): bool
    {
        if (! $this->sameTenant($user, $issue) || $issue->status !== InventoryDocumentStatus::Draft) {
            return false;
        }

        return $this->canActOnDestination($user, $issue->destination);
    }

    public function post(User $user, StockIssue $issue): bool
    {
        if (! $this->sameTenant($user, $issue) || $issue->status !== InventoryDocumentStatus::Draft) {
            return false;
        }

        return $this->canActOnDestination($user, $issue->destination);
    }

    protected function canActOnDestination(User $user, StockIssueDestination $destination): bool
    {
        if ($destination === StockIssueDestination::Transfer) {
            return $user->can('inventory.transfer');
        }

        return $user->can('inventory.issue');
    }
}
