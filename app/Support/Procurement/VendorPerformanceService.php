<?php

namespace App\Support\Procurement;

use App\Enums\PurchaseOrderStatus;
use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\Vendor;
use Illuminate\Support\Facades\Schema;

class VendorPerformanceService
{
    /**
     * @return array{performance_percent: ?float, supplier_rating: ?float, orders: int, on_time_percent: ?float}
     */
    public function metrics(Vendor $vendor, int $companyId, ?int $branchId = null): array
    {
        if (! Schema::hasTable('purchase_orders')) {
            return $this->emptyMetrics();
        }

        $query = PurchaseOrder::query()
            ->where('company_id', $companyId)
            ->where('vendor_id', $vendor->id)
            ->whereNotIn('status', [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Cancelled]);

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $orders = (int) (clone $query)->count();

        if ($orders === 0) {
            return $this->emptyMetrics();
        }

        $fulfilled = (clone $query)->whereIn('status', [
            PurchaseOrderStatus::Received,
            PurchaseOrderStatus::Closed,
            PurchaseOrderStatus::PartiallyReceived,
        ])->count();

        $performancePercent = round(($fulfilled / $orders) * 100, 1);
        $supplierRating = round(min(5, max(1, ($performancePercent / 100) * 5)), 1);

        return [
            'performance_percent' => $performancePercent,
            'supplier_rating' => $supplierRating,
            'orders' => $orders,
            'on_time_percent' => $performancePercent,
        ];
    }

    /**
     * @return array{performance_percent: ?float, supplier_rating: ?float, orders: int, on_time_percent: ?float}
     */
    protected function emptyMetrics(): array
    {
        return [
            'performance_percent' => null,
            'supplier_rating' => null,
            'orders' => 0,
            'on_time_percent' => null,
        ];
    }
}
