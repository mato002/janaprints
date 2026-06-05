<?php

namespace App\Support\Commercial\Reports;

use Illuminate\Support\Facades\Schema;

class CommercialSalesOrderReportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Sales Orders'), 'sales_orders', [
                'company_id', 'branch_id', 'customer_id', 'quotation_id', 'created_by',
                'status', 'order_date', 'required_date', 'total_amount', 'order_number',
            ]),
            $this->row(__('Sales Order Items'), 'sales_order_items', [
                'sales_order_id', 'item_name', 'quantity', 'unit_price', 'line_total',
            ]),
            $this->row(__('Customers'), 'customers', ['company_id', 'branch_id', 'company_name']),
            $this->row(__('Quotations'), 'quotations', [
                'company_id', 'branch_id', 'customer_id', 'status', 'quotation_date', 'quotation_number', 'total_amount',
            ]),
            $this->row(__('Branches'), 'branches', ['company_id', 'name', 'is_active']),
            $this->row(__('Users / Salespersons'), 'users', ['company_id', 'name']),
        ];
    }

    public function isReady(): bool
    {
        return collect($this->assess())->every(fn (array $row) => $row['ready']);
    }

    /**
     * @param  list<string>  $columns
     * @return array{source: string, table: string, ready: bool, notes: string}
     */
    protected function row(string $source, string $table, array $columns): array
    {
        if (! Schema::hasTable($table)) {
            return [
                'source' => $source,
                'table' => $table,
                'ready' => false,
                'notes' => __('Table missing'),
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
        ];
    }
}
