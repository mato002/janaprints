<?php

namespace App\Support\Inventory;

use App\Enums\DocumentType;
use App\Enums\ProcurementItemClassification;
use App\Enums\PurchaseRequestStatus;
use App\Enums\ReorderAlertStatus;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Procurement\PurchaseRequest;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Platform\NumberingService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class ReorderAlertService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(int $companyId, int $branchId, array $filters = []): LengthAwarePaginator
    {
        return $this->query($companyId, $branchId, $filters)
            ->paginate(config('platform.pagination.default', 15));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function query(int $companyId, int $branchId, array $filters = []): Builder
    {
        $query = InventoryReorderAlert::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId)
            ->with(['inventoryItem.category', 'warehouse', 'sourceWarehouse', 'acknowledger', 'resolver'])
            ->latest('alerted_at');

        if (! empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', (int) $filters['warehouse_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->whereHas('inventoryItem', fn (Builder $item) => $item->where('inventory_category_id', (int) $filters['category_id']));
        }

        if (! empty($filters['subcategory_id'])) {
            $query->whereHas('inventoryItem', fn (Builder $item) => $item->where('subcategory_id', (int) $filters['subcategory_id']));
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['critical_only'])) {
            $query->where('current_quantity', '<=', 0);
        }

        if (! empty($filters['search'])) {
            $search = '%'.strtolower(trim((string) $filters['search'])).'%';
            $query->whereHas('inventoryItem', function (Builder $item) use ($search) {
                $item->whereRaw('LOWER(sku) LIKE ?', [$search])
                    ->orWhereRaw('LOWER(item_name) LIKE ?', [$search]);
            });
        }

        return $query;
    }

    public function acknowledge(InventoryReorderAlert $alert, int $userId): InventoryReorderAlert
    {
        if ($alert->status !== ReorderAlertStatus::Open) {
            throw ValidationException::withMessages([
                'status' => __('Only open alerts can be acknowledged.'),
            ]);
        }

        $alert->update([
            'status' => ReorderAlertStatus::Acknowledged,
            'acknowledged_by' => $userId,
            'acknowledged_at' => now(),
        ]);

        ActivityLogger::log('reorder_alert_acknowledged', $alert, $userId);

        return $alert->fresh(['inventoryItem', 'warehouse', 'acknowledger']);
    }

    public function resolve(InventoryReorderAlert $alert, int $userId): InventoryReorderAlert
    {
        if (! $alert->status->isActionable()) {
            throw ValidationException::withMessages([
                'status' => __('Alert is already resolved.'),
            ]);
        }

        $alert->update([
            'status' => ReorderAlertStatus::Resolved,
            'is_resolved' => true,
            'resolved_by' => $userId,
            'resolved_at' => now(),
        ]);

        ActivityLogger::log('reorder_alert_resolved', $alert, $userId);

        return $alert->fresh(['inventoryItem', 'warehouse', 'resolver']);
    }

    public function createPurchaseRequest(InventoryReorderAlert $alert, User $user): PurchaseRequest
    {
        if (! $alert->status->isActionable()) {
            throw ValidationException::withMessages([
                'status' => __('Resolved alerts cannot create purchase requests.'),
            ]);
        }

        $item = $alert->inventoryItem;

        if ($item === null) {
            throw ValidationException::withMessages([
                'item' => __('Inventory item not found for this alert.'),
            ]);
        }

        $shortage = $alert->shortageQuantity();
        $quantity = (float) $alert->recommended_quantity > 0
            ? (float) $alert->recommended_quantity
            : ((float) $alert->reorder_quantity > 0
                ? (float) $alert->reorder_quantity
                : max($shortage, 1));
        $unitCost = (float) $item->standard_cost;

        $purchaseRequest = PurchaseRequest::query()->create([
            'company_id' => $alert->company_id,
            'branch_id' => $alert->branch_id,
            'request_number' => app(NumberingService::class)->next(
                DocumentType::PurchaseRequest,
                $alert->company_id,
                $alert->branch_id,
            ),
            'requested_by' => $user->id,
            'department_id' => null,
            'required_date' => now()->addDays(7)->toDateString(),
            'status' => PurchaseRequestStatus::Draft,
            'reason' => __('Reorder alert for :item (SKU :sku)', [
                'item' => $item->item_name,
                'sku' => $item->sku,
            ]),
            'notes' => __('Created from reorder alert. Warehouse: :warehouse. Current qty: :current, reorder level: :level.', [
                'warehouse' => $alert->warehouse?->name ?? __('Branch'),
                'current' => $alert->current_quantity,
                'level' => $alert->reorder_level,
            ]),
        ]);

        $purchaseRequest->items()->create([
            'inventory_item_id' => $item->id,
            'item_classification' => ProcurementItemClassification::InventoryItem,
            'description' => $item->item_name,
            'quantity' => $quantity,
            'estimated_unit_cost' => $unitCost,
            'line_total' => round($quantity * $unitCost, 2),
        ]);

        ActivityLogger::log('reorder_alert_purchase_request', $alert, $user->id, [
            'purchase_request_id' => $purchaseRequest->id,
            'request_number' => $purchaseRequest->request_number,
        ]);

        return $purchaseRequest->fresh(['items']);
    }

    /**
     * @return array{critical: int, open: int, resolved_today: int}
     */
    public function dashboardCounts(int $companyId, int $branchId): array
    {
        $base = InventoryReorderAlert::query()
            ->where('company_id', $companyId)
            ->where('branch_id', $branchId);

        return [
            'critical' => (clone $base)
                ->whereIn('status', [ReorderAlertStatus::Open, ReorderAlertStatus::Acknowledged])
                ->where('current_quantity', '<=', 0)
                ->count(),
            'open' => (clone $base)
                ->whereIn('status', [ReorderAlertStatus::Open, ReorderAlertStatus::Acknowledged])
                ->count(),
            'resolved_today' => (clone $base)
                ->where('status', ReorderAlertStatus::Resolved)
                ->whereDate('resolved_at', today())
                ->count(),
        ];
    }
}
