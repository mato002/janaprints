<?php

namespace App\Support\Procurement;

use App\Enums\SupplierBillStatus;
use App\Enums\SupplierBillType;
use App\Models\Procurement\SupplierBill;
use Illuminate\Support\Collection;

class SupplierAgingService
{
    /**
     * @param  array{company_id?: int, vendor_id?: int, as_of_date?: string}  $filters
     */
    public function build(array $filters = []): array
    {
        $asOf = $filters['as_of_date'] ?? now()->toDateString();

        $query = SupplierBill::query()
            ->with('vendor')
            ->whereIn('status', [SupplierBillStatus::Posted->value])
            ->where('balance_due', '>', 0)
            ->whereNot('bill_type', SupplierBillType::CreditNote->value);

        if (! empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        } elseif (tenant()->companyId()) {
            $query->where('company_id', tenant()->companyId());
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        $bills = $query->get();
        $asOfDate = \Illuminate\Support\Carbon::parse($asOf);

        $buckets = [
            'current' => 0.0,
            'days_1_30' => 0.0,
            'days_31_60' => 0.0,
            'days_61_90' => 0.0,
            'days_90_plus' => 0.0,
        ];

        $rows = collect();

        foreach ($bills->groupBy('vendor_id') as $vendorId => $vendorBills) {
            $vendor = $vendorBills->first()->vendor;
            $vendorBuckets = array_fill_keys(array_keys($buckets), 0.0);

            foreach ($vendorBills as $bill) {
                $due = ($bill->due_date ?? $bill->bill_date)->copy()->startOfDay();
                $amount = (float) $bill->balance_due;
                $bucket = $asOfDate->copy()->startOfDay()->lte($due)
                    ? 'current'
                    : $this->bucketKey((int) $due->diffInDays($asOfDate));

                $vendorBuckets[$bucket] += $amount;
                $buckets[$bucket] += $amount;
            }

            $total = array_sum($vendorBuckets);

            if ($total > 0) {
                $rows->push([
                    'vendor_id' => $vendorId,
                    'vendor_name' => $vendor?->vendor_name ?? __('Unknown'),
                    'current' => round($vendorBuckets['current'], 2),
                    'days_1_30' => round($vendorBuckets['days_1_30'], 2),
                    'days_31_60' => round($vendorBuckets['days_31_60'], 2),
                    'days_61_90' => round($vendorBuckets['days_61_90'], 2),
                    'days_90_plus' => round($vendorBuckets['days_90_plus'], 2),
                    'total' => round($total, 2),
                ]);
            }
        }

        return [
            'as_of_date' => $asOf,
            'rows' => $rows->sortBy('vendor_name')->values(),
            'totals' => array_map(fn ($v) => round($v, 2), $buckets),
            'grand_total' => round(array_sum($buckets), 2),
        ];
    }

    protected function bucketKey(int $daysOverdue): string
    {
        if ($daysOverdue <= 30) {
            return 'days_1_30';
        }

        if ($daysOverdue <= 60) {
            return 'days_31_60';
        }

        if ($daysOverdue <= 90) {
            return 'days_61_90';
        }

        return 'days_90_plus';
    }
}
