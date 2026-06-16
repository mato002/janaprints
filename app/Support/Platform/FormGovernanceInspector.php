<?php

namespace App\Support\Platform;

class FormGovernanceInspector
{
    /**
     * Registry form keys with confirmed FormSettingsService wiring in controllers.
     *
     * @var list<string>
     */
    protected array $governedForms = [
        'customer',
        'lead',
        'quotation',
        'artwork',
        'sales_order',
        'segment.create',
        'commercial_price_book.create',
        'activity.create',
        'commercial_complaint.create',
        'commercial_support_ticket.create',
        'inventory_item',
        'warehouse.create',
        'warehouse.edit',
        'warehouse.manager_assignment',
        'stock_issue.create',
        'store_transfer.create',
        'stock_receipt.create',
        'stock_count.create',
        'cycle_count_schedule.create',
        'stock_adjustment.create',
        'payroll_run.create',
    ];

    /**
     * @return array{
     *     total_forms: int,
     *     governed_forms: int,
     *     non_governed_forms: int,
     *     compliance_percent: int,
     *     gaps: list<string>
     * }
     */
    public function complianceSummary(): array
    {
        $registryKeys = array_keys(config('form_registry.forms', []));
        $total = count($registryKeys);
        $governed = count(array_intersect($registryKeys, $this->governedForms));
        $gaps = array_values(array_diff($registryKeys, $this->governedForms));
        $nonGoverned = count($gaps);

        return [
            'total_forms' => $total,
            'governed_forms' => $governed,
            'non_governed_forms' => $nonGoverned,
            'compliance_percent' => $total > 0 ? (int) round(($governed / $total) * 100) : 0,
            'gaps' => $gaps,
        ];
    }
}
