<?php

namespace App\Services\EmailIdentity;

use App\DataTransferObjects\EmailIdentity\EmailSenderResolution;
use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Models\Employee;

class EmailSenderResolver
{
    public function __construct(
        protected MailboxAddressResolver $mailboxAddresses,
        protected EmailIdentityAuditService $audit,
    ) {}

    public function resolve(string $purpose, bool $auditFallback = false, ?Employee $employee = null): EmailSenderResolution
    {
        $purpose = strtolower(trim($purpose));
        $mailboxPurpose = (string) config("email_senders.purposes.{$purpose}", $purpose);
        $directAddress = $this->directAddressFor($mailboxPurpose);
        $configured = filled($directAddress);
        $address = $configured ? $directAddress : $this->mailboxAddresses->fallback();
        $usedFallback = ! $configured;

        if ($auditFallback && $usedFallback) {
            $this->auditFallback($purpose, $mailboxPurpose, $address, $employee);
        }

        return new EmailSenderResolution(
            purpose: $purpose,
            mailboxPurpose: $mailboxPurpose,
            address: $address,
            configured: $configured,
            usedFallback: $usedFallback,
        );
    }

    public function resolveOrAbort(string $purpose, bool $auditFallback = false, ?Employee $employee = null): EmailSenderResolution
    {
        $resolution = $this->resolve($purpose, $auditFallback, $employee);

        if (! filled($resolution->address)) {
            throw new \RuntimeException(__('No configured sender address is available for :purpose.', [
                'purpose' => $purpose,
            ]));
        }

        return $resolution;
    }

    protected function directAddressFor(string $mailboxPurpose): ?string
    {
        $department = config("mailboxes.department.{$mailboxPurpose}");
        if (filled($department)) {
            return (string) $department;
        }

        $system = config("mailboxes.system.{$mailboxPurpose}");
        if (filled($system)) {
            return (string) $system;
        }

        return null;
    }

    protected function auditFallback(
        string $purpose,
        string $mailboxPurpose,
        ?string $address,
        ?Employee $employee,
    ): void {
        $properties = [
            'purpose' => $purpose,
            'mailbox_purpose' => $mailboxPurpose,
            'fallback_address' => $address,
        ];

        if ($employee) {
            $this->audit->logForEmployee(MailboxAuditAction::SenderFallbackUsed, $employee, $properties);

            return;
        }

        $this->audit->log(MailboxAuditAction::SenderFallbackUsed, null, $properties);
    }
}
