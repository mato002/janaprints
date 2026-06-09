<?php

namespace App\Support\Inventory;

use App\Enums\StockIssueDestination;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProductionConsumptionGovernance
{
    /**
     * Production stock issues are blocked unless a warehouse manager supplies an audited override.
     */
    public function productionDestinationAllowed(User $user, int $warehouseId): bool
    {
        return $this->canOverrideProductionIssue($user, $warehouseId);
    }

    public function canOverrideProductionIssue(User $user, int $warehouseId): bool
    {
        return $user->can('inventory.issue.production.override')
            && $this->isWarehouseManager($user, $warehouseId);
    }

    public function isWarehouseManager(User $user, int $warehouseId): bool
    {
        return DB::table('user_warehouse')
            ->where('warehouse_id', $warehouseId)
            ->where('user_id', $user->id)
            ->where('is_manager', true)
            ->exists();
    }

    /**
     * @return list<StockIssueDestination>
     */
    public function allowedDestinationsFor(User $user, ?int $warehouseId = null): array
    {
        return array_values(array_filter(
            StockIssueDestination::cases(),
            fn (StockIssueDestination $destination) => $this->destinationAllowedFor($user, $destination, $warehouseId),
        ));
    }

    public function destinationAllowedFor(
        User $user,
        StockIssueDestination $destination,
        ?int $warehouseId = null,
    ): bool {
        if ($destination === StockIssueDestination::Transfer) {
            return $user->can('inventory.transfer');
        }

        if ($destination === StockIssueDestination::Production) {
            return $warehouseId !== null
                && $this->canOverrideProductionIssue($user, $warehouseId);
        }

        return $user->can('inventory.issue');
    }

    public function assertCanUseDestination(
        User $user,
        StockIssueDestination $destination,
        int $warehouseId,
        ?string $overrideReason = null,
    ): void {
        if ($destination === StockIssueDestination::Transfer) {
            abort_unless($user->can('inventory.transfer'), 403);

            return;
        }

        abort_unless($user->can('inventory.issue'), 403);

        if ($destination !== StockIssueDestination::Production) {
            return;
        }

        if (! $this->canOverrideProductionIssue($user, $warehouseId)) {
            throw ValidationException::withMessages([
                'destination' => $this->blockedMessage(),
            ]);
        }

        if (! filled(trim((string) $overrideReason))) {
            throw ValidationException::withMessages([
                'production_override_reason' => __('A reason is required for production stock issue overrides.'),
            ]);
        }
    }

    public function blockedMessage(): string
    {
        return __('Production consumption must be recorded on the job card Materials tab. Generic production stock issues are restricted to audited warehouse manager overrides.');
    }

    public function redirectGuidance(): string
    {
        return __('Open the production job card, go to Materials, and consume from requirements or record manual consumption there.');
    }

    public function recordOverride(StockIssue $issue, User $user, string $reason): void
    {
        ActivityLogger::log('production_stock_issue_override', $issue, $user->id, [
            'issue_number' => $issue->issue_number,
            'warehouse_id' => $issue->warehouse_id,
            'reason' => $reason,
            'destination' => $issue->destination->value,
        ]);
    }

    public function applyOverrideMetadata(StockIssue $issue, User $user, string $reason): void
    {
        $issue->update([
            'production_override_reason' => $reason,
            'production_override_by' => $user->id,
            'production_override_at' => now(),
        ]);

        $this->recordOverride($issue->fresh(), $user, $reason);
    }
}
