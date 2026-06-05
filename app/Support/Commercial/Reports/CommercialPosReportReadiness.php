<?php

namespace App\Support\Commercial\Reports;

use Illuminate\Support\Facades\Schema;

class CommercialPosReportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('POS Sales'), 'pos_sales', [
                'company_id', 'branch_id', 'cashier_id', 'pos_session_id', 'sale_number', 'sale_date',
                'status', 'total_amount', 'created_at',
            ]),
            $this->row(__('POS Payments'), 'pos_payments', ['pos_sale_id', 'payment_method', 'amount']),
            $this->row(__('POS Sale Items'), 'pos_sale_items', ['pos_sale_id', 'quantity', 'line_total'], optional: true),
            $this->row(__('POS Sessions'), 'pos_sessions', [
                'company_id', 'branch_id', 'cashier_id', 'session_number', 'status',
                'opening_float', 'opening_cash', 'expected_cash', 'actual_cash', 'variance',
                'opened_at', 'closed_at',
            ]),
            $this->row(__('POS Returns'), 'pos_returns', [
                'company_id', 'branch_id', 'pos_sale_id', 'return_number', 'status',
                'refund_method', 'refund_amount', 'completed_at', 'created_at',
            ], optional: true),
            $this->row(__('Branches'), 'branches', ['company_id', 'name', 'is_active']),
            $this->row(__('Cashiers'), 'users', ['company_id', 'name']),
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
