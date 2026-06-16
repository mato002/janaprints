<?php

namespace App\Support\Hr;

use App\Models\Hr\PayrollPayslip;
use App\Models\Hr\PayrollRun;
use App\Models\User;
use App\Support\ActivityLogger;

class PayrollAuditService
{
    public function logRunCreated(PayrollRun $run, User $user): void
    {
        ActivityLogger::log('payroll_run_created', $run, $user->id, [
            'reference' => $run->reference,
            'period_start' => $run->period_start?->toDateString(),
            'period_end' => $run->period_end?->toDateString(),
        ]);
    }

    public function logGenerated(PayrollRun $run, User $user): void
    {
        ActivityLogger::log('payroll_generated', $run, $user->id, [
            'employee_count' => $run->employee_count,
            'gross_total' => (float) $run->gross_total,
            'net_total' => (float) $run->net_total,
            'has_warnings' => (bool) $run->has_generation_warnings,
        ]);
    }

    public function logReviewed(PayrollRun $run, User $user, array $review): void
    {
        ActivityLogger::log('payroll_reviewed', $run, $user->id, [
            'critical_count' => $review['summary']['critical_count'] ?? 0,
            'warning_count' => $review['summary']['warning_count'] ?? 0,
        ]);
    }

    public function logSubmittedForApproval(PayrollRun $run, User $user): void
    {
        ActivityLogger::log('payroll_submitted_for_approval', $run, $user->id, [
            'reference' => $run->reference,
            'net_total' => (float) $run->net_total,
        ]);
    }

    public function logApproved(PayrollRun $run, User $user): void
    {
        ActivityLogger::log('payroll_approved', $run, $user->id, [
            'reference' => $run->reference,
        ]);
    }

    public function logRejected(PayrollRun $run, User $user, ?string $reason = null): void
    {
        ActivityLogger::log('payroll_rejected', $run, $user->id, [
            'reference' => $run->reference,
            'reason' => $reason,
        ]);
    }

    public function logPosted(PayrollRun $run, User $user): void
    {
        ActivityLogger::log('payroll_posted', $run, $user->id, [
            'journal_id' => $run->posted_journal_id,
            'net_total' => (float) $run->net_total,
        ]);
    }

    public function logPayslipReleased(PayrollPayslip $payslip, User $user): void
    {
        ActivityLogger::log('payroll_payslip_released', $payslip, $user->id, [
            'employee_id' => $payslip->employee_id,
            'reference' => $payslip->reference,
        ]);
    }

    public function logPaymentExport(PayrollRun $run, User $user, string $format): void
    {
        ActivityLogger::log('payroll_payment_exported', $run, $user->id, [
            'format' => $format,
            'reference' => $run->reference,
        ]);
    }
}
