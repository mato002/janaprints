<?php

namespace App\Support\Commercial\Reports;

use Illuminate\Support\Facades\Schema;

class CommercialCustomerReportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Customers'), 'customers', ['company_id', 'branch_id', 'customer_type', 'status', 'created_at']),
            $this->row(__('Sales Orders'), 'sales_orders', ['company_id', 'branch_id', 'customer_id', 'created_by', 'status', 'order_date', 'total_amount']),
            $this->row(__('Quotations'), 'quotations', ['company_id', 'branch_id', 'customer_id', 'prepared_by', 'status', 'quotation_date', 'total_amount']),
            $this->row(__('Branches'), 'branches', ['company_id', 'name', 'is_active']),
            $this->row(__('Users / Salespersons'), 'users', ['company_id', 'name']),
            $this->row(__('Customer Activities'), 'customer_activities', ['company_id', 'branch_id', 'customer_id', 'activity_type', 'activity_at'], optional: true),
            $this->row(__('Customer Invoices'), 'customer_invoices', ['company_id', 'customer_id', 'invoice_date', 'total_amount', 'status'], optional: true),
            $this->row(__('Leads'), 'leads', ['company_id', 'branch_id', 'status'], optional: true),
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
                'notes' => __('Table missing'),
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
