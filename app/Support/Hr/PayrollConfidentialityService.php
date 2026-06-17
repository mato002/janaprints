<?php

namespace App\Support\Hr;

use App\Models\Communications\CommunicationLog;
use App\Models\Communications\EmailMessage;
use App\Models\User;

class PayrollConfidentialityService
{
    public const REDACTED_PLACEHOLDER = '[Payroll-confidential content redacted]';

    public const CONFIDENTIAL_SENDER_PURPOSES = [
        'payslip',
        'payroll',
        'compensation',
    ];

    public const CONFIDENTIAL_ENTITY_TYPES = [
        'payroll_payslip',
        'payroll_run',
        'employee_compensation',
        'compensation',
    ];

    public function viewerCanSeePayrollConfidential(?User $user = null): bool
    {
        $user ??= auth()->user();

        return $user?->can('hr.payroll.view') ?? false;
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function isConfidentialMetadata(?array $metadata, ?string $senderPurpose = null): bool
    {
        if ($metadata['payroll_confidential'] ?? false) {
            return true;
        }

        if ($senderPurpose !== null && in_array($senderPurpose, self::CONFIDENTIAL_SENDER_PURPOSES, true)) {
            return true;
        }

        $module = (string) ($metadata['module'] ?? '');
        $entityType = (string) ($metadata['entity_type'] ?? '');

        return $module === 'hr' && in_array($entityType, self::CONFIDENTIAL_ENTITY_TYPES, true);
    }

    public function isConfidentialEmailMessage(EmailMessage $message): bool
    {
        $metadata = $message->provider_response['metadata'] ?? [];

        return $this->isConfidentialMetadata(
            is_array($metadata) ? $metadata : [],
            (string) ($message->provider_response['sender_purpose'] ?? ''),
        );
    }

    public function isConfidentialCommunicationLog(CommunicationLog $log): bool
    {
        if (($log->provider_response['payroll_confidential'] ?? false) === true) {
            return true;
        }

        if ($log->source_type === EmailMessage::class && $log->source_id) {
            $message = EmailMessage::query()->find($log->source_id);

            return $message !== null && $this->isConfidentialEmailMessage($message);
        }

        return $this->looksLikePayrollContent((string) $log->subject, (string) $log->message_body);
    }

    public function bodyForCommunicationLog(EmailMessage $message): string
    {
        if (! $this->isConfidentialEmailMessage($message)) {
            return (string) $message->body;
        }

        return $this->redactBody((string) $message->body);
    }

    public function communicationLogBodyForViewer(CommunicationLog $log, ?User $viewer = null): string
    {
        if (! $this->isConfidentialCommunicationLog($log)) {
            return (string) $log->message_body;
        }

        if ($this->viewerCanSeePayrollConfidential($viewer)) {
            if ($log->source_type === EmailMessage::class && $log->source_id) {
                $message = EmailMessage::query()->find($log->source_id);

                if ($message !== null) {
                    return (string) $message->body;
                }
            }

            return (string) $log->message_body;
        }

        return self::REDACTED_PLACEHOLDER;
    }

    public function emailBodyForViewer(EmailMessage $message, ?User $viewer = null): string
    {
        if (! $this->isConfidentialEmailMessage($message)) {
            return (string) $message->body;
        }

        if ($this->viewerCanSeePayrollConfidential($viewer)) {
            return (string) $message->body;
        }

        return self::REDACTED_PLACEHOLDER;
    }

    public function redactBody(string $body): string
    {
        $redacted = preg_replace(
            [
                '/Net pay:\s*[\d,]+(?:\.\d{2})?/iu',
                '/Gross pay:\s*[\d,]+(?:\.\d{2})?/iu',
                '/Basic salary:\s*[\d,]+(?:\.\d{2})?/iu',
                '/(?:KES|USD|EUR)\s*[\d,]+(?:\.\d{2})?/iu',
                '/[\d,]+\.\d{2}/',
            ],
            '[redacted]',
            $body,
        ) ?? $body;

        if ($this->looksLikePayrollContent('', $redacted)) {
            return self::REDACTED_PLACEHOLDER;
        }

        return trim($redacted) === '' || $redacted === $body
            ? self::REDACTED_PLACEHOLDER
            : $redacted;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public function markConfidentialMetadata(array $metadata, ?string $senderPurpose = null): array
    {
        if ($this->isConfidentialMetadata($metadata, $senderPurpose)) {
            $metadata['payroll_confidential'] = true;
        }

        return $metadata;
    }

    protected function looksLikePayrollContent(string $subject, string $body): bool
    {
        $haystack = strtolower($subject.' '.$body);

        foreach (['payslip', 'net pay', 'gross pay', 'payroll', 'compensation', 'salary'] as $needle) {
            if (str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }
}
