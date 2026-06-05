<?php

namespace App\Support\Commercial\Reports;

use Illuminate\Support\Facades\Schema;

class CommercialArtworkReportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Artwork Requests'), 'artwork_requests', [
                'company_id', 'branch_id', 'customer_id', 'quotation_id', 'requested_by',
                'assigned_designer_id', 'priority', 'status', 'due_date', 'current_version', 'created_at',
            ]),
            $this->row(__('Artwork Approvals'), 'artwork_approvals', [
                'company_id', 'branch_id', 'artwork_request_id', 'artwork_version_id', 'approved_by', 'decision', 'created_at',
            ]),
            $this->row(__('Artwork Versions'), 'artwork_versions', [
                'artwork_request_id', 'version_number', 'uploaded_by', 'created_at',
            ]),
            $this->row(__('Customers'), 'customers', ['company_id', 'branch_id', 'company_name', 'customer_code']),
            $this->row(__('Branches'), 'branches', ['company_id', 'name', 'is_active']),
            $this->row(__('Users / Designers'), 'users', ['company_id', 'name']),
            $this->row(__('Quotations'), 'quotations', ['company_id', 'customer_id', 'status'], optional: true),
            $this->row(__('Sales Orders'), 'sales_orders', ['company_id', 'customer_id', 'status'], optional: true),
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
