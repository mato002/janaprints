<?php

namespace App\Support\Hr;

class HrDashboardExporter
{
    public function __construct(
        protected HrDashboardService $dashboard,
        protected HrDashboardIntelligenceService $intelligence,
    ) {}

    /**
     * @return list<array<int, string>>
     */
    public function rows(int $companyId): array
    {
        $overview = $this->dashboard->overview($companyId);
        $snapshot = $this->intelligence->snapshot($companyId);
        $trends = $snapshot['trends'];

        $rows = [
            [__('HR Dashboard Export'), now()->format('Y-m-d H:i')],
            [],
            [__('KPI'), __('Value')],
        ];

        foreach ($snapshot['kpis'] as $kpi) {
            if (! empty($kpi['hidden'])) {
                continue;
            }
            $rows[] = [$kpi['label'], $kpi['value']];
        }

        $rows[] = [];
        $rows[] = [__('Attendance Trend'), __('Value')];
        foreach ($trends['attendance'] as $point) {
            $rows[] = [$point['label'], (string) $point['value']];
        }

        $rows[] = [];
        $rows[] = [__('Payroll Trend'), __('Value')];
        foreach ($trends['payroll'] as $point) {
            $rows[] = [$point['label'], (string) $point['value']];
        }

        $rows[] = [];
        $rows[] = [__('Leave Trend'), __('Value')];
        foreach ($trends['leave'] as $point) {
            $rows[] = [$point['label'], (string) $point['value']];
        }

        $rows[] = [];
        $rows[] = [__('Headcount Trend'), __('Value')];
        foreach ($trends['headcount'] as $point) {
            $rows[] = [$point['label'], (string) $point['value']];
        }

        $rows[] = [];
        $rows[] = [__('Department'), __('Headcount'), __('Active')];
        foreach ($snapshot['widgets']['department_headcount'] as $row) {
            $rows[] = [(string) $row[0], (string) $row[1], (string) $row[2]];
        }

        if (! empty($overview['alerts'])) {
            $rows[] = [];
            $rows[] = [__('Alerts')];
            foreach ($overview['alerts'] as $alert) {
                $rows[] = [$alert['label']];
            }
        }

        return $rows;
    }
}
