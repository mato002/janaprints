<?php

namespace App\Support\Commercial;

use Illuminate\Support\Facades\Schema;

class PosSessionReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('POS Sessions'), 'pos_sessions', [
                'company_id', 'branch_id', 'cashier_id', 'session_number', 'opening_float', 'opening_cash',
                'expected_cash', 'actual_cash', 'variance', 'status', 'opened_at', 'closed_at',
            ]),
            $this->row(__('Counter Sales'), 'pos_sales', [
                'company_id', 'branch_id', 'cashier_id', 'pos_session_id', 'sale_number', 'sale_date', 'status', 'total_amount',
            ]),
            $this->row(__('POS Payments'), 'pos_payments', ['pos_sale_id', 'payment_method', 'amount']),
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
