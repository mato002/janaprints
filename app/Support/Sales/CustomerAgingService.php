<?php

namespace App\Support\Sales;

use App\Enums\CustomerInvoiceStatus;
use App\Enums\CustomerInvoiceType;
use App\Models\Crm\Customer;
use App\Models\Sales\CustomerInvoice;
use Illuminate\Support\Collection;

class CustomerAgingService
{
    /**
     * @param  array{company_id?: int, customer_id?: int, as_of_date?: string}  $filters
     */
    public function build(array $filters = []): array
    {
        $asOf = $filters['as_of_date'] ?? now()->toDateString();

        $query = CustomerInvoice::query()
            ->with('customer')
            ->where('status', CustomerInvoiceStatus::Posted)
            ->where('balance_due', '>', 0)
            ->whereNot('invoice_type', CustomerInvoiceType::CreditNote);

        if (! empty($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        } elseif (tenant()->companyId()) {
            $query->where('company_id', tenant()->companyId());
        }

        if (! empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }

        $invoices = $query->get();
        $asOfDate = \Illuminate\Support\Carbon::parse($asOf);

        $buckets = [
            'current' => 0.0,
            'days_1_30' => 0.0,
            'days_31_60' => 0.0,
            'days_61_90' => 0.0,
            'days_90_plus' => 0.0,
        ];

        $rows = collect();

        foreach ($invoices->groupBy('customer_id') as $customerId => $customerInvoices) {
            $customer = $customerInvoices->first()->customer;
            $customerBuckets = [
                'current' => 0.0,
                'days_1_30' => 0.0,
                'days_31_60' => 0.0,
                'days_61_90' => 0.0,
                'days_90_plus' => 0.0,
            ];

            foreach ($customerInvoices as $invoice) {
                $due = ($invoice->due_date ?? $invoice->invoice_date)->copy()->startOfDay();
                $amount = (float) $invoice->balance_due;
                $bucket = $asOfDate->copy()->startOfDay()->lte($due)
                    ? 'current'
                    : $this->bucketKey((int) $due->diffInDays($asOfDate));

                $customerBuckets[$bucket] += $amount;
                $buckets[$bucket] += $amount;
            }

            $total = array_sum($customerBuckets);

            if ($total > 0) {
                $rows->push([
                    'customer_id' => $customerId,
                    'customer_name' => $customer?->company_name ?? __('Unknown'),
                    'current' => round($customerBuckets['current'], 2),
                    'days_1_30' => round($customerBuckets['days_1_30'], 2),
                    'days_31_60' => round($customerBuckets['days_31_60'], 2),
                    'days_61_90' => round($customerBuckets['days_61_90'], 2),
                    'days_90_plus' => round($customerBuckets['days_90_plus'], 2),
                    'total' => round($total, 2),
                ]);
            }
        }

        return [
            'as_of_date' => $asOf,
            'rows' => $rows->sortBy('customer_name')->values(),
            'totals' => array_map(fn ($v) => round($v, 2), $buckets),
            'grand_total' => round(array_sum($buckets), 2),
        ];
    }

    protected function bucketKey(int $daysOverdue): string
    {
        if ($daysOverdue <= 0) {
            return 'current';
        }

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
