<?php

namespace App\Support\Procurement\Performance;

use Illuminate\Support\Facades\Schema;

class SupplierPerformanceReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Vendors'), 'vendors', ['company_id', 'vendor_name', 'status']),
            $this->row(__('Purchase Orders'), 'purchase_orders', ['company_id', 'branch_id', 'vendor_id', 'order_date', 'expected_delivery_date', 'status', 'total_amount']),
            $this->row(__('Purchase Order Items'), 'purchase_order_items', ['purchase_order_id', 'quantity', 'quantity_received']),
            $this->row(__('Goods Receipts'), 'goods_receipts', ['purchase_order_id', 'receipt_date', 'status']),
            $this->row(__('Goods Receipt Items'), 'goods_receipt_items', ['goods_receipt_id', 'quantity_received']),
            $this->row(__('RFQs'), 'rfqs', ['company_id', 'awarded_vendor_id', 'status']),
            $this->row(__('RFQ Vendors'), 'rfq_vendors', ['rfq_id', 'vendor_id', 'invitation_status', 'invited_at', 'responded_at']),
            $this->row(__('Supplier Quotations'), 'supplier_quotations', ['vendor_id', 'status', 'quotation_date'], optional: true),
            $this->row(__('Vendor Comparisons'), 'vendor_comparisons', ['rfq_id', 'recommended_vendor_id', 'matrix'], optional: true),
        ];
    }

    public function isReady(): bool
    {
        return collect($this->assess())
            ->filter(fn (array $row) => ! ($row['optional'] ?? false))
            ->every(fn (array $row) => $row['ready']);
    }

    /**
     * @param  list<string>  $columns
     * @return array{source: string, table: string, ready: bool, notes: string, optional?: bool}
     */
    protected function row(string $source, string $table, array $columns, bool $optional = false): array
    {
        if (! Schema::hasTable($table)) {
            return [
                'source' => $source,
                'table' => $table,
                'ready' => false,
                'notes' => $optional
                    ? __('Not available — related metric will be limited')
                    : __('Table missing'),
                'optional' => $optional,
            ];
        }

        $missing = collect($columns)->filter(fn (string $col) => ! Schema::hasColumn($table, $col));

        return [
            'source' => $source,
            'table' => $table,
            'ready' => $missing->isEmpty(),
            'notes' => $missing->isEmpty()
                ? __('Operational data available')
                : __('Missing columns: :cols', ['cols' => $missing->implode(', ')]),
            'optional' => $optional,
        ];
    }
}
