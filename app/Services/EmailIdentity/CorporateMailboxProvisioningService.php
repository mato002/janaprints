<?php

namespace App\Services\EmailIdentity;

use App\DataTransferObjects\EmailIdentity\CpanelMailboxResult;
use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Enums\EmailIdentity\MailboxStatus;
use App\Enums\EmailIdentity\MailboxType;
use App\Models\EmailIdentity\CorporateMailbox;
use App\Models\Employee;
use Illuminate\Support\Str;

class CorporateMailboxProvisioningService
{
    public function __construct(
        protected CorporateEmailGeneratorService $emailGenerator,
        protected CpanelMailboxGateway $cpanelGateway,
        protected EmailIdentityAuditService $audit,
    ) {}

    public function generateForEmployee(Employee $employee): string
    {
        $email = $this->emailGenerator->generate(
            $employee->first_name,
            $employee->last_name,
            $employee->id,
        );

        $this->audit->logForEmployee(MailboxAuditAction::MailboxGenerated, $employee, [
            'generated_email' => $email,
        ]);

        return $email;
    }

    public function provisionForEmployee(Employee $employee, string $corporateEmail): CorporateMailbox
    {
        [$localPart, $domain] = explode('@', Str::lower($corporateEmail), 2);

        $mailbox = CorporateMailbox::query()->updateOrCreate(
            ['employee_id' => $employee->id, 'type' => MailboxType::Corporate],
            [
                'company_id' => $employee->company_id,
                'email_address' => $corporateEmail,
                'local_part' => $localPart,
                'domain' => $domain,
                'status' => MailboxStatus::Pending,
                'provision_error' => null,
            ],
        );

        $password = Str::password(24);
        $result = $this->cpanelGateway->createMailbox(
            $corporateEmail,
            $password,
        );

        $this->applyProvisioningResult($mailbox, $result);

        return $mailbox->fresh();
    }

    public function suspend(CorporateMailbox $mailbox): CorporateMailbox
    {
        $result = $this->cpanelGateway->suspendMailbox($mailbox->email_address);

        if ($result->success) {
            $mailbox->update([
                'status' => MailboxStatus::Suspended,
                'metadata' => array_merge($mailbox->metadata ?? [], [
                    'last_suspend' => now()->toIso8601String(),
                    'cpanel' => $result->metadata,
                ]),
            ]);

            $this->audit->logForMailbox(MailboxAuditAction::MailboxSuspended, $mailbox);
        } else {
            $mailbox->update([
                'provision_error' => $result->error,
                'metadata' => array_merge($mailbox->metadata ?? [], [
                    'last_suspend_attempt' => now()->toIso8601String(),
                    'cpanel' => $result->metadata,
                ]),
            ]);
        }

        return $mailbox->fresh();
    }

    protected function applyProvisioningResult(CorporateMailbox $mailbox, CpanelMailboxResult $result): void
    {
        if ($result->success) {
            $mailbox->update([
                'status' => MailboxStatus::Pending,
                'provisioned_at' => now(),
                'provision_error' => null,
                'metadata' => array_merge($mailbox->metadata ?? [], [
                    'cpanel' => $result->metadata,
                ]),
            ]);

            $this->audit->logForMailbox(MailboxAuditAction::MailboxCreated, $mailbox, [
                'mocked' => $result->metadata['mocked'] ?? false,
            ]);

            return;
        }

        $mailbox->update([
            'provision_error' => $result->error,
            'metadata' => array_merge($mailbox->metadata ?? [], [
                'cpanel' => $result->metadata,
            ]),
        ]);
    }
}
