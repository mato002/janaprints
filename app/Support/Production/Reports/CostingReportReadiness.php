<?php

namespace App\Support\Production\Reports;

use Illuminate\Support\Facades\Schema;

class CostingReportReadiness
{
    /**
     * @return list<array{source: string, table: string, ready: bool, notes: string, optional?: bool}>
     */
    public function assess(): array
    {
        return [
            $this->row(__('Job Cost Sheets'), 'job_cost_sheets', ['company_id', 'branch_id', 'production_job_card_id', 'material_cost', 'total_cost', 'revenue', 'gross_profit', 'gross_margin_percent', 'calculated_at']),
            $this->row(__('Production Job Cards'), 'production_job_cards', ['company_id', 'branch_id', 'job_card_number', 'customer_id', 'production_type']),
            $this->row(__('Material Consumptions'), 'production_material_consumptions', ['production_job_card_id', 'inventory_item_id', 'quantity', 'unit_cost', 'consumed_at']),
            $this->row(__('Inventory Items'), 'inventory_items', ['company_id', 'inventory_category_id', 'item_name'], optional: true),
            $this->row(__('Inventory Categories'), 'inventory_categories', ['company_id', 'code', 'name'], optional: true),
            $this->row(__('Wastage Tracking'), 'production_material_consumptions', ['is_wastage'], optional: true),
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
                    ? __('Not available — metric will be limited')
                    : __('Table missing'),
                'optional' => $optional,
            ];
        }

        $missing = collect($columns)->filter(fn (string $col) => ! Schema::hasColumn($table, $col));

        if ($optional && $missing->contains('is_wastage')) {
            return [
                'source' => $source,
                'table' => $table,
                'ready' => false,
                'notes' => __('Waste % will display as unavailable until wastage tracking is activated'),
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
