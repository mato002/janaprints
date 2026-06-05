<?php

namespace App\Support\Commercial;

use Illuminate\Support\Facades\Schema;

class PosCashReconciliationReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('POS Sessions'), 'pos_sessions', ['company_id', 'branch_id', 'cashier_id', 'opening_float', 'expected_cash', 'actual_cash', 'variance', 'status', 'closed_at']),
            $this->row(__('POS Sales'), 'pos_sales', ['company_id', 'branch_id', 'pos_session_id', 'status', 'total_amount']),
            $this->row(__('POS Payments'), 'pos_payments', ['pos_sale_id', 'payment_method', 'amount']),
            $this->row(__('Cash Reconciliations'), 'pos_cash_reconciliations', ['company_id', 'branch_id', 'pos_session_id', 'expected_cash', 'actual_cash', 'variance', 'status']),
            $this->row(__('Reconciliation Audit'), 'pos_cash_reconciliation_logs', ['pos_cash_reconciliation_id', 'user_id', 'action', 'created_at']),
            $this->row(__('Branches'), 'branches', ['company_id', 'name', 'is_active']),
            $this->row(__('Cashiers'), 'users', ['company_id', 'name']),
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
