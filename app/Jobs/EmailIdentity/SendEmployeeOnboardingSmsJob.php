<?php

namespace App\Jobs\EmailIdentity;

use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Jobs\PlatformJob;
use App\Models\Employee;
use App\Models\Integrations\IntegrationSmsSetting;
use App\Services\EmailIdentity\EmailIdentityAuditService;
use App\Support\Communications\Bridge\IntegrationSmsDriver;

class SendEmployeeOnboardingSmsJob extends PlatformJob
{
    public function __construct(
        public int $employeeId,
        public string $phone,
    ) {
        parent::__construct();
        $this->useQueue('emails');
    }

    public function handle(
        EmailIdentityAuditService $audit,
        IntegrationSmsDriver $driver,
    ): void {
        $employee = Employee::query()->find($this->employeeId);

        if (! $employee || ! filled($this->phone)) {
            return;
        }

        if (! config('employee_onboarding.sms.enabled', true)) {
            $audit->logForEmployee(MailboxAuditAction::OnboardingSmsSkipped, $employee, [
                'reason' => 'disabled_in_config',
                'phone' => $this->phone,
            ]);

            return;
        }

        $setting = IntegrationSmsSetting::query()
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->first();

        if (! $setting) {
            $audit->logForEmployee(MailboxAuditAction::OnboardingSmsSkipped, $employee, [
                'reason' => 'provider_not_configured',
                'phone' => $this->phone,
            ]);

            return;
        }

        $body = $this->buildMessage($employee);

        try {
            $message = new \App\Models\Communications\SmsMessage([
                'company_id' => $employee->company_id,
                'branch_id' => $employee->branch_id,
                'phone_number' => $this->phone,
                'message_body' => $body,
                'segments_count' => (int) max(1, ceil(strlen($body) / 160)),
            ]);

            $result = $driver->send($setting, $message);

            if ($result->success) {
                $setting->increment('sms_sent_today');
                $setting->increment('sms_sent_month');

                $audit->logForEmployee(MailboxAuditAction::OnboardingSmsSent, $employee, [
                    'phone' => $this->phone,
                    'provider' => $setting->provider->value,
                    'provider_message_id' => $result->providerMessageId,
                ]);

                return;
            }

            $audit->logForEmployee(MailboxAuditAction::OnboardingSmsFailed, $employee, [
                'phone' => $this->phone,
                'provider' => $setting->provider->value,
                'error' => $result->error,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            $audit->logForEmployee(MailboxAuditAction::OnboardingSmsFailed, $employee, [
                'phone' => $this->phone,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    protected function buildMessage(Employee $employee): string
    {
        $message = (string) config('employee_onboarding.sms.message');

        if (config('employee_onboarding.sms.include_activation_link', false)) {
            $activation = $employee->activations()
                ->whereNull('activated_at')
                ->latest('id')
                ->first();

            if ($activation && filled($activation->activation_url ?? null)) {
                $message .= ' '.__('Activate: :url', ['url' => $activation->activation_url]);
            }
        }

        return trim($message);
    }
}
