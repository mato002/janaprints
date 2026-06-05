<?php

namespace App\Support\Commercial;

use App\Enums\ArtworkRequestStatus;
use App\Enums\QuotationStatus;
use App\Enums\SalesOrderStatus;
use App\Models\Artwork\ArtworkRequest;
use App\Models\Sales\Quotation;
use App\Models\Sales\SalesOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CommercialApprovalQueueService
{
    /**
     * @return array{
     *     pending_quotations: Collection<int, array<string, mixed>>,
     *     pending_sales_orders: Collection<int, array<string, mixed>>,
     *     pending_artwork: Collection<int, array<string, mixed>>,
     *     recently_approved: Collection<int, array<string, mixed>>,
     *     recently_rejected: Collection<int, array<string, mixed>>,
     * }
     */
    public function present(int $companyId, ?int $branchId = null): array
    {
        return [
            'pending_quotations' => $this->pendingQuotations($companyId, $branchId),
            'pending_sales_orders' => $this->pendingSalesOrders($companyId, $branchId),
            'pending_artwork' => $this->pendingArtwork($companyId, $branchId),
            'recently_approved' => $this->recentlyApproved($companyId, $branchId),
            'recently_rejected' => $this->recentlyRejected($companyId, $branchId),
        ];
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function pendingQuotations(int $companyId, ?int $branchId): Collection
    {
        return $this->quotationQuery($companyId, $branchId)
            ->where('status', QuotationStatus::PendingApproval)
            ->with(['customer:id,company_name', 'branch:id,name', 'preparer:id,name'])
            ->orderBy('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (Quotation $q) => $this->mapQuotationRow($q, 'pending'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function pendingSalesOrders(int $companyId, ?int $branchId): Collection
    {
        return $this->salesOrderQuery($companyId, $branchId)
            ->where('status', SalesOrderStatus::Draft)
            ->with(['customer:id,company_name', 'branch:id,name', 'creator:id,name'])
            ->orderBy('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (SalesOrder $o) => $this->mapSalesOrderRow($o, 'pending'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function pendingArtwork(int $companyId, ?int $branchId): Collection
    {
        return $this->artworkQuery($companyId, $branchId)
            ->where('status', ArtworkRequestStatus::Submitted)
            ->with(['customer:id,company_name', 'branch:id,name', 'assignedDesigner:id,name'])
            ->orderBy('updated_at')
            ->limit(50)
            ->get()
            ->map(fn (ArtworkRequest $a) => $this->mapArtworkRow($a, 'pending'));
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function recentlyApproved(int $companyId, ?int $branchId): Collection
    {
        $quotes = $this->quotationQuery($companyId, $branchId)
            ->whereNotNull('approved_at')
            ->where('approved_at', '>=', now()->subDays(14))
            ->with(['customer:id,company_name', 'branch:id,name', 'approver:id,name'])
            ->orderByDesc('approved_at')
            ->limit(15)
            ->get()
            ->map(fn (Quotation $q) => $this->mapQuotationRow($q, 'approved'));

        $artwork = $this->artworkQuery($companyId, $branchId)
            ->where('status', ArtworkRequestStatus::Approved)
            ->where('updated_at', '>=', now()->subDays(14))
            ->with(['customer:id,company_name', 'branch:id,name'])
            ->orderByDesc('updated_at')
            ->limit(10)
            ->get()
            ->map(fn (ArtworkRequest $a) => $this->mapArtworkRow($a, 'approved'));

        return $quotes->concat($artwork)->sortByDesc('submitted_at')->values()->take(25);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function recentlyRejected(int $companyId, ?int $branchId): Collection
    {
        return $this->quotationQuery($companyId, $branchId)
            ->where('status', QuotationStatus::Rejected)
            ->where('updated_at', '>=', now()->subDays(14))
            ->with(['customer:id,company_name', 'branch:id,name', 'preparer:id,name'])
            ->orderByDesc('updated_at')
            ->limit(25)
            ->get()
            ->map(fn (Quotation $q) => $this->mapQuotationRow($q, 'rejected'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapQuotationRow(Quotation $quotation, string $bucket): array
    {
        $submittedAt = $quotation->status === QuotationStatus::PendingApproval
            ? $quotation->updated_at
            : ($quotation->approved_at ?? $quotation->updated_at);

        return [
            'type' => 'quotation',
            'document' => $quotation->quotation_number,
            'customer' => $quotation->customer?->company_name ?? __('Walk-in'),
            'branch' => $quotation->branch?->name ?? '—',
            'amount' => number_format((float) $quotation->total_amount, 2),
            'requested_by' => $quotation->preparer?->name ?? $quotation->approver?->name ?? '—',
            'submitted_at' => $submittedAt,
            'age_days' => $submittedAt ? $submittedAt->diffInDays(now()) : 0,
            'status' => $quotation->status->value,
            'status_label' => str($quotation->status->value)->headline(),
            'view_url' => route('admin.quotations.show', $quotation),
            'approve_url' => $bucket === 'pending' ? route('admin.quotations.approve', $quotation) : null,
            'reject_url' => $bucket === 'pending' ? route('admin.quotations.reject', $quotation) : null,
            'bucket' => $bucket,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapSalesOrderRow(SalesOrder $order, string $bucket): array
    {
        return [
            'type' => 'sales_order',
            'document' => $order->order_number,
            'customer' => $order->customer?->company_name ?? '—',
            'branch' => $order->branch?->name ?? '—',
            'amount' => number_format((float) $order->total_amount, 2),
            'requested_by' => $order->creator?->name ?? '—',
            'submitted_at' => $order->updated_at,
            'age_days' => $order->updated_at?->diffInDays(now()) ?? 0,
            'status' => $order->status->value,
            'status_label' => str($order->status->value)->headline(),
            'view_url' => route('admin.sales-orders.show', $order),
            'approve_url' => $bucket === 'pending' ? route('admin.sales-orders.confirm', $order) : null,
            'reject_url' => null,
            'bucket' => $bucket,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function mapArtworkRow(ArtworkRequest $request, string $bucket): array
    {
        return [
            'type' => 'artwork',
            'document' => $request->request_number,
            'customer' => $request->customer?->company_name ?? '—',
            'branch' => $request->branch?->name ?? '—',
            'amount' => '—',
            'requested_by' => $request->assignedDesigner?->name ?? '—',
            'submitted_at' => $request->updated_at,
            'age_days' => $request->updated_at?->diffInDays(now()) ?? 0,
            'status' => $request->status->value,
            'status_label' => str($request->status->value)->headline(),
            'view_url' => route('admin.artwork.show', $request),
            'approve_url' => $bucket === 'pending' ? route('admin.artwork.approve', $request) : null,
            'reject_url' => null,
            'bucket' => $bucket,
        ];
    }

    protected function quotationQuery(int $companyId, ?int $branchId): Builder
    {
        return Quotation::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }

    protected function salesOrderQuery(int $companyId, ?int $branchId): Builder
    {
        return SalesOrder::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }

    protected function artworkQuery(int $companyId, ?int $branchId): Builder
    {
        return ArtworkRequest::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
    }
}
