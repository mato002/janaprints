<?php

namespace App\Support\Commercial\Reports;

use Illuminate\Support\Facades\Schema;

class CommercialQuotationReportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Quotations'), 'quotations', [
                'company_id', 'branch_id', 'customer_id', 'prepared_by', 'approved_by', 'approved_at',
                'status', 'quotation_date', 'valid_until', 'total_amount', 'quotation_number',
            ]),
            $this->row(__('Quotation Items'), 'quotation_items', [
                'quotation_id', 'item_name', 'quantity', 'unit_price', 'line_total',
            ]),
            $this->row(__('Customers'), 'customers', ['company_id', 'branch_id', 'company_name']),
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
     * @return array{source: string, table: string, ready: bool, notes: string, optional?: bool}
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
