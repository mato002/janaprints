<?php

namespace App\Services\Dispatch;

use App\Enums\Dispatch\DeliveryNoteStatus;
use App\Enums\ProductionJobCardStatus;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Production\ProductionJobCard;

class DispatchDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function build(int $companyId, ?int $branchId = null): array
    {
        $notes = DeliveryNote::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        $jobs = ProductionJobCard::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));

        return [
            'ready_jobs' => (clone $jobs)->where('status', ProductionJobCardStatus::ReadyForDispatch)->count(),
            'draft_notes' => (clone $notes)->where('status', DeliveryNoteStatus::Draft)->count(),
            'dispatched_notes' => (clone $notes)->where('status', DeliveryNoteStatus::Dispatched)->count(),
            'delivered_notes' => (clone $notes)->where('status', DeliveryNoteStatus::Delivered)->count(),
            'delivered_today' => (clone $notes)
                ->where('status', DeliveryNoteStatus::Delivered)
                ->whereDate('delivered_at', now()->toDateString())
                ->count(),
            'invoice_ready' => (clone $notes)->where('invoice_ready', true)->count(),
            'recent_notes' => (clone $notes)
                ->with(['customer:id,company_name', 'productionJobCard:id,job_card_number'])
                ->latest('updated_at')
                ->limit(8)
                ->get(['id', 'delivery_note_number', 'customer_id', 'production_job_card_id', 'status', 'delivery_date', 'dispatched_at', 'delivered_at']),
        ];
    }
}
