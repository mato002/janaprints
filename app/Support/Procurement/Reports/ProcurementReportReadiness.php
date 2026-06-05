<?php

namespace App\Support\Procurement\Reports;

use Illuminate\Support\Facades\Schema;

class ProcurementReportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Vendors'), 'vendors', ['company_id', 'vendor_name', 'status']),
            $this->row(__('Purchase Orders'), 'purchase_orders', ['company_id', 'branch_id', 'vendor_id', 'order_date', 'status', 'total_amount']),
            $this->row(__('Purchase Order Items'), 'purchase_order_items', ['purchase_order_id', 'inventory_item_id', 'line_total']),
            $this->row(__('Goods Receipts'), 'goods_receipts', ['company_id', 'branch_id', 'purchase_order_id', 'warehouse_id', 'receipt_date', 'status']),
            $this->row(__('Warehouses (filter)'), 'warehouses', ['company_id', 'branch_id', 'name'], optional: true),
            $this->row(__('Categories (filter)'), 'inventory_categories', ['company_id', 'name'], optional: true),
            $this->row(__('Inventory Items (filter)'), 'inventory_items', ['company_id', 'category_id'], optional: true),
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
                    ? __('Not available — filter or metric will be limited')
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
