<?php

namespace App\Services\EmailIdentity;

use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Jobs\EmailIdentity\SendEmployeeOnboardingSmsJob;
use App\Models\Employee;
use App\Models\Integrations\IntegrationSmsSetting;

class EmployeeOnboardingSmsNotifier
{
    public function __construct(
        protected EmailIdentityAuditService $audit,
    ) {}

    public function notifyIfConfigured(Employee $employee): void
    {
        if (! filled($employee->phone)) {
            return;
        }

        try {
            if (! $this->isProviderConfigured($employee->company_id)) {
                $this->audit->logForEmployee(MailboxAuditAction::OnboardingSmsSkipped, $employee, [
                    'reason' => 'provider_not_configured',
                    'phone' => $employee->phone,
                ]);

                return;
            }

            SendEmployeeOnboardingSmsJob::dispatch(
                employeeId: $employee->id,
                phone: (string) $employee->phone,
            );
        } catch (\Throwable $exception) {
            report($exception);

            $this->audit->logForEmployee(MailboxAuditAction::OnboardingSmsSkipped, $employee, [
                'reason' => 'dispatch_failed',
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function isProviderConfigured(?int $companyId): bool
    {
        if (! $companyId) {
            return false;
        }

        return IntegrationSmsSetting::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->exists();
    }
}
