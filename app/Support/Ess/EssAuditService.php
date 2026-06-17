<?php

namespace App\Support\Ess;

use App\Models\Employee;
use App\Models\User;
use App\Support\ActivityLogger;

class EssAuditService
{
    public function logProfileUpdated(Employee $employee, User $actor, array $changes): void
    {
        ActivityLogger::log('ess_profile_updated', $employee, $actor->getKey(), [
            'employee_id' => $employee->getKey(),
            'changes' => $changes,
        ]);
    }

    public function logDocumentDownloaded(Employee $employee, User $actor, int $documentId, string $label): void
    {
        ActivityLogger::log('ess_document_downloaded', $employee, $actor->getKey(), [
            'document_id' => $documentId,
            'label' => $label,
        ]);
    }

    public function logPayslipDownloaded(Employee $employee, User $actor, int $payslipId, string $reference): void
    {
        ActivityLogger::log('ess_payslip_downloaded', $employee, $actor->getKey(), [
            'payslip_id' => $payslipId,
            'reference' => $reference,
        ]);
    }

    public function logPasswordChanged(User $user): void
    {
        if ($user->employee_id === null) {
            return;
        }

        ActivityLogger::log('ess_password_changed', $user->employee, $user->getKey(), [
            'user_id' => $user->getKey(),
        ]);
    }

    public function logSessionsTerminated(User $user, int $count, string $reason): void
    {
        if ($user->employee_id === null) {
            return;
        }

        ActivityLogger::log('ess_sessions_terminated', $user->employee, $user->getKey(), [
            'count' => $count,
            'reason' => $reason,
        ]);
    }
}
