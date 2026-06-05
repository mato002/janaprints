<?php

namespace App\Support\Commercial\Reports;

use Illuminate\Support\Facades\Schema;

class CommercialConversionReportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Leads'), 'leads', [
                'company_id', 'branch_id', 'lead_source_id', 'assigned_to', 'customer_id', 'status', 'created_at',
            ]),
            $this->row(__('Customers'), 'customers', ['company_id', 'branch_id', 'customer_type', 'company_name']),
            $this->row(__('Quotations'), 'quotations', [
                'company_id', 'branch_id', 'customer_id', 'prepared_by', 'status', 'quotation_date', 'quotation_number',
            ]),
            $this->row(__('Sales Orders'), 'sales_orders', [
                'company_id', 'branch_id', 'customer_id', 'created_by', 'status', 'order_date', 'order_number',
            ]),
            $this->row(__('Artwork Requests'), 'artwork_requests', [
                'company_id', 'branch_id', 'customer_id', 'quotation_id', 'status', 'created_at',
            ], optional: true),
            $this->row(__('Production Job Cards'), 'production_job_cards', [
                'company_id', 'branch_id', 'sales_order_id', 'customer_id', 'status', 'created_at',
            ], optional: true),
            $this->row(__('Delivery Notes'), 'delivery_notes', [
                'company_id', 'branch_id', 'sales_order_id', 'status', 'delivery_date', 'dispatched_at', 'delivered_at',
            ], optional: true),
            $this->row(__('Lead Sources'), 'lead_sources', ['company_id', 'name', 'is_active']),
            $this->row(__('Branches'), 'branches', ['company_id', 'name', 'is_active']),
            $this->row(__('Users / Salespersons'), 'users', ['company_id', 'name']),
        ];
    }

    public function isReady(): bool
    {
        return collect($this->assess())
            ->filter(fn (array $row) => ! ($row['optional'] ?? false))
            ->every(fn (array $row) => $row['ready']);
    }

    public function hasProductionPipeline(): bool
    {
        return Schema::hasTable('production_job_cards')
            && Schema::hasColumn('production_job_cards', 'company_id');
    }

    public function hasDispatchPipeline(): bool
    {
        return Schema::hasTable('delivery_notes')
            && Schema::hasColumn('delivery_notes', 'company_id');
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
