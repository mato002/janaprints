<?php

namespace App\Support\Procurement;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\RfqStatus;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BuyDeskWorkQueueService
{
    public function __construct(
        protected ProcurementApprovalQueueService $approvals,
    ) {}

    /**
     * @return array{
     *     summary: list<array<string, mixed>>,
     *     items: list<array<string, mixed>>,
     *     counts: array<string, int>,
     *     health: array<string, mixed>,
     *     needs_attention: list<array<string, mixed>>
     * }
     */
    public function present(Request $request, int $companyId, ?int $branchId): array
    {
        $user = $request->user();
        $today = now()->startOfDay();

        $openRequests = PurchaseRequest::query()
            ->forTenant()
            ->whereIn('status', [
                PurchaseRequestStatus::Submitted,
                PurchaseRequestStatus::PendingApproval,
                PurchaseRequestStatus::Approved,
            ])
            ->count();

        $draftRequests = PurchaseRequest::query()
            ->forTenant()
            ->where('status', PurchaseRequestStatus::Draft)
            ->count();

        $openRfqs = Rfq::query()
            ->forTenant()
            ->whereIn('status', [
                RfqStatus::Draft,
                RfqStatus::Open,
                RfqStatus::Closed,
                RfqStatus::AwaitingComparison,
                RfqStatus::Awarded,
            ])
            ->count();

        $awaitingComparison = Rfq::query()
            ->forTenant()
            ->where('status', RfqStatus::AwaitingComparison)
            ->count();

        $closingSoon = Rfq::query()
            ->forTenant()
            ->where('status', RfqStatus::Open)
            ->whereNotNull('closing_date')
            ->whereBetween('closing_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();

        $openOrders = PurchaseOrder::query()
            ->forTenant()
            ->whereIn('status', [
                PurchaseOrderStatus::Draft,
                PurchaseOrderStatus::PendingApproval,
                PurchaseOrderStatus::Approved,
                PurchaseOrderStatus::Sent,
                PurchaseOrderStatus::PartiallyReceived,
            ])
            ->count();

        $awaitingReceipt = PurchaseOrder::query()
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

        $draftReceipts = GoodsReceipt::query()
            ->forTenant()
            ->where('status', GoodsReceiptStatus::Draft)
            ->count();

        $pendingApprovals = 0;
        if ($user?->can('procurement.approvals.view')) {
            $pendingApprovals = $this->approvals->present($companyId, $branchId, $user)['pending']->count();
        }

        $counts = [
            'open_requests' => $openRequests,
            'draft_requests' => $draftRequests,
            'open_rfqs' => $openRfqs,
            'awaiting_comparison' => $awaitingComparison,
            'closing_soon' => $closingSoon,
            'open_orders' => $openOrders,
            'awaiting_receipt' => $awaitingReceipt,
            'overdue_deliveries' => $overdueDeliveries,
            'draft_receipts' => $draftReceipts,
            'pending_approvals' => $pendingApprovals,
        ];

        $summary = [
            $this->summaryCard(
                __('Open Requests'),
                $openRequests,
                'amber',
                $openRequests > 0,
                ProcurementDeskViews::deskUrl(ProcurementDeskViews::REQUESTS),
            ),
            $this->summaryCard(
                __('Approvals'),
                $pendingApprovals,
                'rose',
                $pendingApprovals > 0,
                ProcurementDeskViews::deskUrl(ProcurementDeskViews::APPROVALS),
            ),
            $this->summaryCard(
                __('Open RFQs'),
                $openRfqs,
                'blue',
                $openRfqs > 0,
                ProcurementDeskViews::deskUrl(ProcurementDeskViews::RFQS),
            ),
            $this->summaryCard(
                __('Awaiting Receipt'),
                $awaitingReceipt,
                'emerald',
                $awaitingReceipt > 0,
                ProcurementDeskViews::deskUrl(ProcurementDeskViews::ORDERS),
            ),
        ];

        return [
            'summary' => $summary,
            'items' => $this->queueItems($today),
            'counts' => $counts,
            'health' => $this->buyHealth($counts),
            'needs_attention' => $this->needsAttention($counts, $user),
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return array{percent: int, label: string, tone: string, detail: string}
     */
    protected function buyHealth(array $counts): array
    {
        $pressure = min(30, (int) $counts['pending_approvals'] * 5)
            + min(25, (int) $counts['overdue_deliveries'] * 6)
            + min(15, (int) $counts['awaiting_comparison'] * 4)
            + min(15, (int) $counts['closing_soon'] * 3)
            + min(10, (int) $counts['draft_receipts'] * 2)
            + min(10, (int) $counts['open_requests'] * 1);

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
                ? __('Buying pipeline is within normal thresholds.')
                : __('Exceptions need attention before supply stalls.'),
        ];
    }

    /**
     * @param  array<string, int>  $counts
     * @return list<array<string, mixed>>
     */
    protected function needsAttention(array $counts, ?User $user): array
    {
        $items = [];

        if ($counts['pending_approvals'] > 0) {
            $items[] = [
                'key' => 'approvals',
                'severity' => 'critical',
                'label' => __('Pending Approvals'),
                'count' => $counts['pending_approvals'],
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::APPROVALS),
                'modal' => false,
            ];
        }

        if ($counts['overdue_deliveries'] > 0) {
            $items[] = [
                'key' => 'overdue',
                'severity' => 'critical',
                'label' => __('Late Deliveries'),
                'count' => $counts['overdue_deliveries'],
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::ORDERS),
                'modal' => false,
            ];
        }

        if ($counts['awaiting_comparison'] > 0) {
            $items[] = [
                'key' => 'compare',
                'severity' => 'warning',
                'label' => __('Awaiting Comparison'),
                'count' => $counts['awaiting_comparison'],
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::RFQS),
                'modal' => false,
            ];
        }

        if ($counts['closing_soon'] > 0) {
            $items[] = [
                'key' => 'closing',
                'severity' => 'warning',
                'label' => __('RFQs Closing Soon'),
                'count' => $counts['closing_soon'],
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::RFQS),
                'modal' => false,
            ];
        }

        if ($counts['draft_receipts'] > 0) {
            $items[] = [
                'key' => 'receipts',
                'severity' => 'warning',
                'label' => __('Draft Receipts'),
                'count' => $counts['draft_receipts'],
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::RECEIPTS),
                'modal' => false,
            ];
        }

        if ($counts['open_requests'] > 0) {
            $items[] = [
                'key' => 'requests',
                'severity' => 'warning',
                'label' => __('Open Requests'),
                'count' => $counts['open_requests'],
                'url' => ProcurementDeskViews::deskUrl(ProcurementDeskViews::REQUESTS),
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

    /**
     * @return list<array<string, mixed>>
     */
    protected function queueItems(Carbon $today): array
    {
        $items = collect();

        PurchaseRequest::query()
            ->forTenant()
            ->whereIn('status', [
                PurchaseRequestStatus::Submitted,
                PurchaseRequestStatus::PendingApproval,
                PurchaseRequestStatus::Approved,
            ])
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->each(function (PurchaseRequest $request) use ($items) {
                $items->push([
                    'kind' => 'request',
                    'label' => $request->request_number,
                    'title' => __('Purchase request'),
                    'meta' => $request->reason ?: '—',
                    'status' => $request->status?->name ?? (string) $request->status,
                    'tone' => 'amber',
                    'priority' => $request->status === PurchaseRequestStatus::Approved ? 2 : 1,
                    'url' => route('admin.procurement.requests.show', $request),
                ]);
            });

        Rfq::query()
            ->forTenant()
            ->whereIn('status', [
                RfqStatus::Open,
                RfqStatus::AwaitingComparison,
                RfqStatus::Awarded,
            ])
            ->latest('updated_at')
            ->limit(6)
            ->get()
            ->each(function (Rfq $rfq) use ($items, $today) {
                $closing = $rfq->closing_date?->startOfDay();
                $urgent = $closing !== null && $closing->lte($today->copy()->addDays(3));

                $items->push([
                    'kind' => 'rfq',
                    'label' => $rfq->rfq_number,
                    'title' => match ($rfq->status) {
                        RfqStatus::AwaitingComparison => __('Compare quotes'),
                        RfqStatus::Awarded => __('Convert to PO'),
                        default => __('Open RFQ'),
                    },
                    'meta' => $closing?->format('d M Y') ?? '—',
                    'status' => $urgent ? __('Due soon') : __('Active'),
                    'tone' => 'blue',
                    'priority' => $urgent ? 1 : 3,
                    'url' => route('admin.procurement.rfqs.show', $rfq),
                ]);
            });

        PurchaseOrder::query()
            ->forTenant()
            ->with(['vendor:id,vendor_name'])
            ->whereIn('status', [
                PurchaseOrderStatus::Approved,
                PurchaseOrderStatus::Sent,
                PurchaseOrderStatus::PartiallyReceived,
            ])
            ->orderByRaw('CASE WHEN expected_delivery_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expected_delivery_date')
            ->limit(6)
            ->get()
            ->each(function (PurchaseOrder $order) use ($items, $today) {
                $expected = $order->expected_delivery_date?->startOfDay();
                $overdue = $expected !== null && $expected->lt($today);

                $items->push([
                    'kind' => 'order',
                    'label' => $order->po_number,
                    'title' => __('Receive from :vendor', ['vendor' => $order->vendor?->vendor_name ?? __('supplier')]),
                    'meta' => $expected?->format('d M Y') ?? __('No date'),
                    'status' => $overdue ? __('Overdue') : __('Awaiting'),
                    'tone' => $overdue ? 'rose' : 'emerald',
                    'priority' => $overdue ? 1 : 4,
                    'url' => route('admin.procurement.orders.show', $order),
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
