<?php

namespace App\Services\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;
use Illuminate\Http\Request;

class DispatchDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null, ?Request $request = null): array
    {
        $request ??= request();
        $statusFilter = (string) $request->query('status', '');

        $notes = DeliveryNote::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $jobs = ProductionJobCard::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $readyCount = (clone $jobs)->where('status', ProductionJobCardStatus::ReadyForDispatch)->count();
        $draftCount = (clone $notes)->where('status', DeliveryNoteStatus::Draft)->count();
        $dispatchedCount = (clone $notes)->where('status', DeliveryNoteStatus::Dispatched)->count();
        $deliveredCount = (clone $notes)->where('status', DeliveryNoteStatus::Delivered)->count();
        $deliveredToday = (clone $notes)
            ->where('status', DeliveryNoteStatus::Delivered)
            ->whereDate('delivered_at', now()->toDateString())
            ->count();

        $readyJobs = (clone $jobs)
            ->where('status', ProductionJobCardStatus::ReadyForDispatch)
            ->with([
                'customer:id,company_name',
                'inventoryItem:id,item_name,sku',
                'salesOrder:id,order_number,required_date',
            ])
            ->latest('created_at')
            ->latest('id')
            ->limit(25)
            ->get(['id', 'job_card_number', 'customer_id', 'inventory_item_id', 'sales_order_id', 'required_date', 'status']);

        $notesQuery = (clone $notes)
            ->with(['customer:id,company_name', 'productionJobCard:id,job_card_number'])
            ->when($statusFilter !== '', fn ($q) => $q->where('status', $statusFilter))
            ->latest('updated_at')
            ->latest('id');

        return [
            'summary' => [
                ['label' => __('Jobs ready'), 'value' => (string) $readyCount, 'filter' => ['focus' => 'ready']],
                ['label' => __('Draft notes'), 'value' => (string) $draftCount, 'filter' => ['status' => DeliveryNoteStatus::Draft->value, 'focus' => 'notes']],
                ['label' => __('Dispatched'), 'value' => (string) $dispatchedCount, 'filter' => ['status' => DeliveryNoteStatus::Dispatched->value, 'focus' => 'notes']],
                ['label' => __('Delivered'), 'value' => (string) $deliveredCount, 'filter' => ['status' => DeliveryNoteStatus::Delivered->value, 'focus' => 'notes']],
                ['label' => __('Delivered today'), 'value' => (string) $deliveredToday, 'filter' => ['status' => DeliveryNoteStatus::Delivered->value, 'focus' => 'notes']],
            ],
            'ready_jobs' => $readyJobs,
            'ready_jobs_count' => $readyCount,
            'notes' => $notesQuery->paginate(15)->withQueryString(),
            'filter_status' => $statusFilter,
            'invoice_ready' => (clone $notes)->where('invoice_ready', true)->count(),
            'ownership' => app(DispatchInventoryReportService::class)->ownershipSummary($companyId, $branchId),
            'can_create_note' => auth()->user()?->can('create', DeliveryNote::class) ?? false,
        ];
    }
}
