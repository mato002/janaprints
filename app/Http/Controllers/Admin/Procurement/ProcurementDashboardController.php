<?php

namespace App\Http\Controllers\Admin\Procurement;

use App\Enums\GoodsReceiptStatus;
use App\Enums\PurchaseOrderStatus;
use App\Enums\PurchaseRequestStatus;
use App\Enums\RfqStatus;
use App\Enums\VendorStatus;
use App\Http\Controllers\Controller;
use App\Models\Procurement\GoodsReceipt;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseRequest;
use App\Models\Procurement\Rfq;
use App\Models\Procurement\Vendor;
use Illuminate\View\View;

class ProcurementDashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('viewAny', Vendor::class);

        $stats = [
            'active_vendors' => Vendor::query()->forTenant()->where('status', VendorStatus::Active)->count(),
            'pending_requests' => PurchaseRequest::query()->forTenant()->whereIn('status', [
                PurchaseRequestStatus::Submitted->value,
                PurchaseRequestStatus::PendingApproval->value,
                PurchaseRequestStatus::Approved->value,
            ])->count(),
            'pending_orders' => PurchaseOrder::query()->forTenant()->whereIn('status', [
                PurchaseOrderStatus::PendingApproval->value,
                PurchaseOrderStatus::Approved->value,
                PurchaseOrderStatus::Sent->value,
            ])->count(),
            'awaiting_receipt' => PurchaseOrder::query()->forTenant()->whereIn('status', [
                PurchaseOrderStatus::Approved->value,
                PurchaseOrderStatus::Sent->value,
                PurchaseOrderStatus::PartiallyReceived->value,
            ])->count(),
            'recent_receipts' => GoodsReceipt::query()->forTenant()
                ->where('status', GoodsReceiptStatus::Posted)
                ->latest('posted_at')
                ->limit(5)
                ->count(),
            'open_rfqs' => Rfq::query()->forTenant()->where('status', RfqStatus::Open)->count(),
            'closing_soon' => Rfq::query()->forTenant()
                ->where('status', RfqStatus::Open)
                ->whereNotNull('closing_date')
                ->whereBetween('closing_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                ->count(),
            'awaiting_comparison' => Rfq::query()->forTenant()->where('status', RfqStatus::AwaitingComparison)->count(),
            'awarded_rfqs' => Rfq::query()->forTenant()->where('status', RfqStatus::Awarded)->count(),
            'converted_rfqs' => Rfq::query()->forTenant()->where('status', RfqStatus::ConvertedToPo)->count(),
        ];

        return view('admin.procurement.dashboard', compact('stats'));
    }
}
