<?php

namespace App\Support\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\ReorderAlertStatus;
use App\Enums\StockCountStatus;
use App\Models\Inventory\InventoryMovement;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\StockCount;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StoreDeskWorkQueueService
{
    /**
     * @return array{
     *     summary: list<array<string, mixed>>,
     *     items: list<array<string, mixed>>,
     *     counts: array<string, int>,
     *     health: array<string, mixed>,
     *     needs_attention: list<array<string, mixed>>
     * }
     */
    public function present(Request $request): array
    {
        $today = now()->startOfDay();
        $user = $request->user();

        $pendingReceipts = StockReceipt::query()
            ->forTenant()
            ->where('status', InventoryDocumentStatus::Draft)
            ->count();

        $pendingIssues = StockIssue::query()
            ->forTenant()
            ->where('status', InventoryDocumentStatus::Draft)
            ->count();

        $lowStockAlerts = InventoryReorderAlert::query()
            ->forTenant()
            ->where('status', '!=', ReorderAlertStatus::Resolved)
            ->count();

        $openStockCounts = StockCount::query()
            ->forTenant()
            ->whereIn('status', [
                StockCountStatus::Draft,
                StockCountStatus::InProgress,
                StockCountStatus::Submitted,
                StockCountStatus::Approved,
            ])
            ->count();

        $awaitingPo = PurchaseOrder::query()
            ->forTenant()
            ->whereIn('status', [
                PurchaseOrderStatus::Approved,
                PurchaseOrderStatus::Sent,
                PurchaseOrderStatus::PartiallyReceived,
            ])
            ->count();

        $overdueDeliveries = PurchaseOrder::query()
            ->forTenant()
            ->whereIn('status', [
                PurchaseOrderStatus::Approved,
                PurchaseOrderStatus::Sent,
                PurchaseOrderStatus::PartiallyReceived,
            ])
            ->whereNotNull('expected_delivery_date')
            ->whereDate('expected_delivery_date', '<', now()->toDateString())
            ->count();

        $negativeStock = $this->negativeBalanceCount();

        $counts = [
            'pending_receipts' => $pendingReceipts,
            'pending_issues' => $pendingIssues,
            'low_stock_alerts' => $lowStockAlerts,
            'open_stock_counts' => $openStockCounts,
            'awaiting_po' => $awaitingPo,
            'overdue_deliveries' => $overdueDeliveries,
            'negative_stock' => $negativeStock,
        ];

        $summary = [
            $this->summaryCard(
                __('Pending Receipts'),
                $pendingReceipts,
                'amber',
                $pendingReceipts > 0,
                StoreDeskViews::deskUrl(StoreDeskViews::RECEIPTS),
            ),
            $this->summaryCard(
                __('Pending Issues'),
                $pendingIssues,
                'rose',
                $pendingIssues > 0,
                StoreDeskViews::deskUrl(StoreDeskViews::ISSUES),
            ),
            $this->summaryCard(
                __('Low Stock'),
                $lowStockAlerts,
                'amber',
                $lowStockAlerts > 0,
                StoreDeskViews::deskUrl(StoreDeskViews::ALERTS),
            ),
            $this->summaryCard(
                __('Awaiting PO'),
                $awaitingPo,
                'blue',
                $awaitingPo > 0,
                route('admin.procurement.orders.index'),
            ),
        ];

        return [
            'summary' => $summary,
            'items' => $this->queueItems($request, $today),
            'counts' => $counts,
            'health' => $this->storeHealth($counts),
            'needs_attention' => $this->needsAttention($counts, $user),
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return array{percent: int, label: string, tone: string, detail: string}
     */
    protected function storeHealth(array $counts): array
    {
        $pressure = min(35, (int) $counts['low_stock_alerts'] * 4)
            + min(20, (int) $counts['pending_issues'] * 3)
            + min(15, (int) $counts['overdue_deliveries'] * 5)
            + min(15, (int) $counts['open_stock_counts'] * 3)
            + min(10, (int) $counts['negative_stock'] * 5)
            + min(10, (int) $counts['pending_receipts'] * 2);

        $percent = max(0, 100 - $pressure);

        [$label, $tone] = match (true) {
            $percent >= 90 => [__('Healthy'), 'emerald'],
            $percent >= 70 => [__('Watch'), 'amber'],
            default => [__('At risk'), 'rose'],
        };

        return [
            'percent' => $percent,
            'label' => $label,
            'tone' => $tone,
            'detail' => $percent >= 90
                ? __('Warehouse is operating within normal thresholds.')
                : __('Exceptions need attention before they hit the floor.'),
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array<string, mixed>>
     */
    protected function needsAttention(array $counts, $user): array
    {
        $items = [];

        if ($counts['low_stock_alerts'] > 0) {
            $items[] = [
                'key' => 'low_stock',
                'severity' => 'critical',
                'label' => __('Low Stock'),
                'count' => $counts['low_stock_alerts'],
                'url' => route('admin.store.desk.reorder-alerts'),
                'modal' => true,
            ];
        }

        if ($counts['overdue_deliveries'] > 0 || $counts['awaiting_po'] > 0) {
            $items[] = [
                'key' => 'awaiting_delivery',
                'severity' => $counts['overdue_deliveries'] > 0 ? 'critical' : 'warning',
                'label' => $counts['overdue_deliveries'] > 0
                    ? __('Late deliveries')
                    : __('Awaiting Delivery'),
                'count' => max($counts['overdue_deliveries'], $counts['awaiting_po']),
                'url' => route('admin.procurement.orders.index'),
                'modal' => false,
            ];
        }

        if ($counts['negative_stock'] > 0) {
            $items[] = [
                'key' => 'negative_stock',
                'severity' => 'critical',
                'label' => __('Negative Stock'),
                'count' => $counts['negative_stock'],
                'url' => StoreDeskViews::deskUrl(StoreDeskViews::BALANCES),
                'modal' => false,
            ];
        }

        if ($counts['open_stock_counts'] > 0) {
            $items[] = [
                'key' => 'counts',
                'severity' => 'warning',
                'label' => __('Counts Pending'),
                'count' => $counts['open_stock_counts'],
                'url' => route('admin.inventory.stock-counts.index'),
                'modal' => false,
            ];
        }

        if ($counts['pending_issues'] > 0) {
            $items[] = [
                'key' => 'issues',
                'severity' => 'warning',
                'label' => __('Issues Waiting'),
                'count' => $counts['pending_issues'],
                'url' => StoreDeskViews::deskUrl(StoreDeskViews::ISSUES),
                'modal' => false,
            ];
        }

        if ($counts['pending_receipts'] > 0) {
            $items[] = [
                'key' => 'receipts',
                'severity' => 'warning',
                'label' => __('Receipts Waiting'),
                'count' => $counts['pending_receipts'],
                'url' => StoreDeskViews::deskUrl(StoreDeskViews::RECEIPTS),
                'modal' => false,
            ];
        }

        if ($items === [] && $user) {
            $items[] = [
                'key' => 'clear',
                'severity' => 'ok',
                'label' => __('No exceptions'),
                'count' => 0,
                'url' => null,
                'modal' => false,
            ];
        }

        return $items;
    }

    protected function negativeBalanceCount(): int
    {
        $sub = InventoryMovement::query()
            ->forTenant()
            ->select('inventory_item_id', 'warehouse_id')
            ->groupBy('inventory_item_id', 'warehouse_id')
            ->havingRaw('SUM(quantity) < 0');

        return (int) DB::query()->fromSub($sub, 'negative_balances')->count();
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function queueItems(Request $request, Carbon $today): array
    {
        $user = $request->user();
        $items = collect();

        StockReceipt::query()
            ->forTenant()
            ->with(['warehouse:id,name', 'items.inventoryItem:id,item_name'])
            ->where('status', InventoryDocumentStatus::Draft)
            ->latest('receipt_date')
            ->limit(8)
            ->get()
            ->each(function (StockReceipt $receipt) use ($items, $today, $user) {
                $firstItem = $receipt->items->first()?->inventoryItem?->item_name ?? __('Goods');
                $dueToday = $receipt->receipt_date?->startOfDay()?->lte($today) ?? true;

                $items->push([
                    'kind' => 'receipt',
                    'label' => $receipt->receipt_number,
                    'title' => __('Receive :item', ['item' => $firstItem]),
                    'meta' => $receipt->warehouse?->name ?? '—',
                    'status' => $dueToday ? __('Due now') : __('Waiting'),
                    'tone' => 'emerald',
                    'priority' => $dueToday ? 1 : 3,
                    'url' => route('admin.inventory.receipts.show', [$receipt, 'from' => 'store-desk']),
                    'modal' => true,
                    'can_post' => $user?->can('post', $receipt) ?? false,
                    'post_url' => route('admin.inventory.receipts.post', $receipt),
                ]);
            });

        StockIssue::query()
            ->forTenant()
            ->with(['warehouse:id,name', 'items.inventoryItem:id,item_name'])
            ->where('status', InventoryDocumentStatus::Draft)
            ->latest('issue_date')
            ->limit(8)
            ->get()
            ->each(function (StockIssue $issue) use ($items, $today, $user) {
                $firstItem = $issue->items->first()?->inventoryItem?->item_name ?? __('Materials');
                $dueToday = $issue->issue_date?->startOfDay()?->lte($today) ?? true;

                $items->push([
                    'kind' => 'issue',
                    'label' => $issue->issue_number,
                    'title' => __('Issue :item', ['item' => $firstItem]),
                    'meta' => $issue->warehouse?->name ?? '—',
                    'status' => $dueToday ? __('Due now') : __('Waiting'),
                    'tone' => 'rose',
                    'priority' => $dueToday ? 2 : 4,
                    'url' => route('admin.inventory.issues.show', [$issue, 'from' => 'store-desk']),
                    'modal' => true,
                    'can_post' => $user?->can('post', $issue) ?? false,
                    'post_url' => route('admin.inventory.issues.post', $issue),
                ]);
            });

        StockCount::query()
            ->forTenant()
            ->with(['warehouse:id,name'])
            ->whereIn('status', [
                StockCountStatus::Draft,
                StockCountStatus::InProgress,
                StockCountStatus::Submitted,
                StockCountStatus::Approved,
            ])
            ->latest('count_date')
            ->limit(6)
            ->get()
            ->each(function (StockCount $count) use ($items, $today) {
                $dueToday = $count->count_date?->startOfDay()?->lte($today) ?? true;

                $items->push([
                    'kind' => 'count',
                    'label' => $count->count_number,
                    'title' => match ($count->count_type) {
                        \App\Enums\StockCountType::Full => __('Full count'),
                        \App\Enums\StockCountType::Partial => __('Partial count'),
                        default => __('Stock count'),
                    },
                    'meta' => $count->warehouse?->name ?? '—',
                    'status' => $dueToday ? __('Due now') : __('Waiting'),
                    'tone' => 'blue',
                    'priority' => $dueToday ? 2 : 5,
                    'url' => route('admin.inventory.stock-counts.worksheet', [$count, 'from' => 'store-desk']),
                    'modal' => true,
                    'can_post' => false,
                ]);
            });

        return $items
            ->sortBy('priority')
            ->take(12)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    protected function summaryCard(string $label, int $value, string $tone, bool $highlight, ?string $url = null): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'tone' => $tone,
            'highlight' => $highlight,
            'url' => $url,
        ];
    }
}
