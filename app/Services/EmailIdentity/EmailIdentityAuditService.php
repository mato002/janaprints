<?php

namespace App\Services\EmailIdentity;

use App\Enums\EmailIdentity\MailboxAuditAction;
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

    public function logForEmployee(MailboxAuditAction $action, Employee $employee, array $metadata = []): void
    {
        $this->log($action, $employee, array_merge([
            'employee_id' => $employee->id,
            'personal_email' => $employee->email,
        ], $metadata));
    }
}
