<?php

namespace App\Support\Communications\Email;

use App\Models\Communications\EmailAccount;
use App\Models\Communications\EmailMessage;
use App\Enums\EmailDeliveryStatus;

class EmailCommunicationCertificationService
{
    public function __construct(
        protected EmailDeliveryDiagnosticsService $diagnostics,
        protected EmailVisibilityService $visibility,
        protected EmailAttachmentIntegrityInspector $attachments,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function report(int $companyId): array
    {
        $diagnostics = $this->diagnostics->forCompany($companyId);
        $health = $this->visibility->communicationHealth($companyId);
        $departments = $this->visibility->departmentReport($companyId);
        $senders = $this->visibility->topSendersByDepartment($companyId);
        $attachmentReport = $this->attachments->inspect($companyId);
        $retentionDays = (int) config('communications.retention_days', 3650);

        $checks = [
            $this->check('smtp_configured', ($diagnostics['smtp']['status'] ?? '') === 'configured', __('SMTP integration is configured')),
            $this->check('delivery_engine', (bool) ($diagnostics['delivery_engine']['active'] ?? false), __('Delivery engine is active')),
            $this->check('queue_active', (bool) ($diagnostics['queue']['active'] ?? false), __('Email queue connection is healthy')),
            $this->check('queue_depth_ok', ($diagnostics['queue']['depth'] ?? 0) < config('communications.queue.critical_backlog', 50), __('Queue depth below critical threshold')),
            $this->check('no_stuck_sending', ($diagnostics['queue']['stuck_sending'] ?? 0) === 0, __('No stuck sending messages')),
            $this->check('failure_rate_ok', ($health['failure_rate'] ?? 0) < 20, __('Failure rate below critical threshold (7d)')),
            $this->check('attachments_integrity', $attachmentReport['healthy'], __('Attachment storage integrity verified')),
            $this->check('retention_configured', $retentionDays > 0, __('Retention policy configured')),
            $this->check('senders_configured', $this->sendersConfigured($companyId), __('Department sender accounts exist')),
        ];

        $passed = collect($checks)->where('passed', true)->count();
        $total = count($checks);
        $score = $total > 0 ? (int) round(($passed / $total) * 100) : 0;

        return [
            'readiness_score' => $score,
            'verdict' => $score >= 90 ? 'certified' : 'blocked',
            'verdict_label' => $score >= 90 ? __('Communication Platform Certified') : __('Certification Blocked'),
            'checks' => $checks,
            'checks_passed' => $passed,
            'checks_total' => $total,
            'health' => $health,
            'diagnostics' => $diagnostics,
            'departments' => $departments,
            'senders' => $senders,
            'failure_rate' => $health['failure_rate'],
            'retention' => [
                'days' => $retentionDays,
                'label' => __(':days days (policy only — no automatic deletion)', ['days' => number_format($retentionDays)]),
                'auto_delete' => false,
            ],
            'attachments' => $attachmentReport,
            'smtp' => [
                'status' => $diagnostics['smtp']['status'] ?? 'unknown',
                'label' => $diagnostics['smtp']['label'] ?? __('Unknown'),
                'ready' => ($diagnostics['smtp']['status'] ?? '') === 'configured',
            ],
            'queue' => [
                'name' => $diagnostics['queue']['name'] ?? 'emails',
                'driver' => $diagnostics['queue']['driver'] ?? '',
                'active' => (bool) ($diagnostics['queue']['active'] ?? false),
                'depth' => $diagnostics['queue']['depth'] ?? 0,
                'queued' => $diagnostics['queue']['queued_count'] ?? 0,
                'stuck_sending' => $diagnostics['queue']['stuck_sending'] ?? 0,
                'failed' => $diagnostics['queue']['failed_count'] ?? 0,
                'cancelled' => $diagnostics['queue']['cancelled_count'] ?? 0,
                'ready' => (bool) ($diagnostics['queue']['active'] ?? false)
                    && ($diagnostics['queue']['stuck_sending'] ?? 0) === 0,
            ],
        ];
    }

    /**
     * @return array{key: string, passed: bool, label: string}
     */
    protected function check(string $key, bool $passed, string $label): array
    {
        return [
            'key' => $key,
            'passed' => $passed,
            'label' => $label,
        ];
    }

    protected function sendersConfigured(int $companyId): bool
    {
        return EmailAccount::query()
            ->where('company_id', $companyId)
            ->whereNotNull('from_email')
            ->exists();
    }
}
