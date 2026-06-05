<?php

namespace App\Support\Inventory\Reports;

use Illuminate\Support\Facades\Schema;

class InventoryReportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Inventory Items'), 'inventory_items', ['company_id', 'branch_id', 'sku', 'item_name', 'reorder_level']),
            $this->row(__('Stock Valuations'), 'inventory_valuations', ['company_id', 'branch_id', 'inventory_item_id', 'warehouse_id', 'quantity_on_hand', 'average_unit_cost']),
            $this->row(__('Warehouses'), 'warehouses', ['company_id', 'branch_id', 'name', 'is_active']),
            $this->row(__('Inventory Movements'), 'inventory_movements', ['company_id', 'branch_id', 'inventory_item_id', 'warehouse_id', 'movement_type', 'quantity', 'movement_date']),
            $this->row(__('Categories'), 'inventory_categories', ['company_id', 'name'], optional: true),
            $this->row(__('Reorder Alerts'), 'inventory_reorder_alerts', ['company_id', 'branch_id', 'inventory_item_id', 'current_quantity', 'reorder_level'], optional: true),
            $this->row(__('Suppliers (filter)'), 'purchase_orders', ['company_id', 'vendor_id'], optional: true),
            $this->row(__('Stock Reservations'), 'inventory_reservations', [], optional: true),
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

        if ($table === 'inventory_reservations') {
            return [
                'source' => $source,
                'table' => $table,
                'ready' => false,
                'notes' => __('Reserved quantity is reported as zero until reservations are implemented'),
                'optional' => true,
            ];
        }

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
