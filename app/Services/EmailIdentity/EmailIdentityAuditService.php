<?php

namespace App\Services\EmailIdentity;

use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Models\EmailIdentity\CorporateMailbox;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;

class EmailIdentityAuditService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function log(MailboxAuditAction $action, ?Model $subject = null, array $metadata = []): void
    {
        try {
            \App\Support\ActivityLogger::log(
                $action->value,
                $subject,
                userId: isset($metadata['user_id']) ? (int) $metadata['user_id'] : null,
                properties: array_merge(['module' => 'email_identity'], $metadata),
            );
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function logForMailbox(MailboxAuditAction $action, CorporateMailbox $mailbox, array $metadata = []): void
    {
        $this->log($action, $mailbox, array_merge([
            'email' => $mailbox->email_address,
            'employee_id' => $mailbox->employee_id,
            'status' => $mailbox->status?->value,
        ], $metadata));
    }

    public function logForEmployee(MailboxAuditAction $action, Employee $employee, array $metadata = []): void
    {
        $this->log($action, $employee, array_merge([
            'employee_id' => $employee->id,
            'corporate_email' => $employee->corporate_email,
            'personal_email' => $employee->email,
        ], $metadata));
    }
}
