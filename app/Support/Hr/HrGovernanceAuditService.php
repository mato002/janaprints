<?php

namespace App\Support\Hr;

use App\Models\Employee;
use App\Models\Hr\EmployeeExit;
use App\Models\User;
use App\Support\ActivityLogger;

class HrGovernanceAuditService
{
    public function logEmployeeSuspended(Employee $employee, User $actor, ?string $reason = null): void
    {
        ActivityLogger::log('employee_suspended', $employee, $employee->getKey(), [
            'employee_number' => $employee->employee_number,
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
        ]);
    }

    public function logEmployeeReactivated(Employee $employee, User $actor, ?string $reason = null): void
    {
        ActivityLogger::log('employee_reactivated', $employee, $employee->getKey(), [
            'employee_number' => $employee->employee_number,
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
        ]);
    }

    public function logEmployeeExitAccessLocked(EmployeeExit $exit, User $actor, int $terminatedSessions = 0): void
    {
        ActivityLogger::log('employee_exit_access_locked', $exit->employee, $exit->employee_id, [
            'exit_id' => $exit->getKey(),
            'exit_reference' => $exit->reference,
            'actor_id' => $actor->getKey(),
            'terminated_sessions' => $terminatedSessions,
        ]);
    }

    public function logUserAccessDeactivated(User $target, User $actor, string $reason): void
    {
        ActivityLogger::log('user_access_deactivated', $target, $target->getKey(), [
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
            'employee_id' => $target->employee_id,
        ]);
    }

    public function logRolesRevoked(User $target, User $actor, string $reason): void
    {
        ActivityLogger::log('user_roles_revoked', $target, $target->getKey(), [
            'actor_id' => $actor->getKey(),
            'reason' => $reason,
        ]);
    }

    public function logPayrollCommunicationRedacted(int $emailMessageId, ?int $communicationLogId = null): void
    {
        ActivityLogger::log('payroll_communication_redacted', null, $emailMessageId, [
            'email_message_id' => $emailMessageId,
            'communication_log_id' => $communicationLogId,
        ]);
    }
}
