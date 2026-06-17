<?php

namespace App\Support\Hr;

use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;

class PayrollFrozenSnapshotService
{
    /**
     * @return array<string, mixed>
     */
    public function build(PayrollRun $run): array
    {
        $run->loadMissing('payslips');

        return [
            'frozen_at' => now()->toIso8601String(),
            'payroll_group' => $run->payroll_group,
            'employee_count' => (int) $run->employee_count,
            'gross_total' => (float) $run->gross_total,
            'deductions_total' => (float) $run->deductions_total,
            'net_total' => (float) $run->net_total,
            'payslip_checksum' => md5(
                $run->payslips
                    ->sortBy('id')
                    ->map(fn (PayrollPayslip $p) => $p->id.':'.number_format((float) $p->net_pay, 2, '.', ''))
                    ->implode('|'),
            ),
        ];
    }

    public function matches(PayrollRun $run): bool
    {
        if (! is_array($run->frozen_snapshot) || $run->frozen_snapshot === []) {
            return true;
        }

        $current = $this->build($run);

        return ($run->frozen_snapshot['payslip_checksum'] ?? null) === $current['payslip_checksum']
            && (float) ($run->frozen_snapshot['net_total'] ?? 0) === $current['net_total'];
    }
}
