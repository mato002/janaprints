<?php

namespace App\Support\Inventory;

use App\Enums\InventoryDocumentStatus;
use App\Enums\ReorderAlertStatus;
use App\Enums\StockCountStatus;
use App\Models\Inventory\InventoryItem;
use App\Models\Inventory\InventoryReorderAlert;
use App\Models\Inventory\StockCount;
use App\Models\Inventory\StockIssue;
use App\Models\Inventory\StockReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StoreDeskWorkQueueService
{
    /**
     * @return array{summary: list<array<string, mixed>>, items: list<array<string, mixed>>}
     */
    public function present(Request $request): array
    {
        $today = now()->startOfDay();

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

        $totalItems = InventoryItem::query()->forTenant()->where('is_active', true)->count();

        $summary = [
            $this->summaryCard(__('Pending Receipts'), $pendingReceipts, 'amber', $pendingReceipts > 0),
            $this->summaryCard(__('Pending Issues'), $pendingIssues, 'rose', $pendingIssues > 0),
            $this->summaryCard(__('Low Stock Alerts'), $lowStockAlerts, 'amber', $lowStockAlerts > 0),
            $this->summaryCard(__('Open Counts'), $openStockCounts, 'blue', $openStockCounts > 0),
            $this->summaryCard(__('Catalogue Items'), $totalItems, 'slate', false),
        ];

        return [
            'summary' => $summary,
            'items' => $this->queueItems($request, $today),
            'counts' => [
                'pending_receipts' => $pendingReceipts,
                'pending_issues' => $pendingIssues,
                'low_stock_alerts' => $lowStockAlerts,
                'open_stock_counts' => $openStockCounts,
                'total_items' => $totalItems,
            ],
        ];
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
    protected function summaryCard(string $label, int $value, string $tone, bool $highlight): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'tone' => $tone,
            'highlight' => $highlight,
        ];
    }
}
