<?php

namespace App\Jobs\EmailIdentity;

use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Jobs\PlatformJob;
use App\Mail\EmployeeOnboardingMail;
use App\Models\Employee;
use App\Services\EmailIdentity\EmailIdentityAuditService;
use App\Services\EmailIdentity\EmailSenderResolver;
use App\Support\Branding\BrandingAssets;
use App\Support\Communications\Email\CorporateMailDispatcher;

class SendEmployeeOnboardingEmailJob extends PlatformJob
{
    public function __construct(
        public int $employeeId,
        public string $personalEmail,
        public string $activationUrl,
        public string $expiresAt,
    ) {
        parent::__construct();
        $this->useQueue('emails');
    }

    public function handle(
        EmailIdentityAuditService $audit,
        EmailSenderResolver $senderResolver,
        BrandingAssets $branding,
        CorporateMailDispatcher $mail,
    ): void {
        $employee = Employee::query()->with('company')->find($this->employeeId);

        if (! $employee) {
            return;
        }

        $sender = $senderResolver->resolveOrAbort('employee_onboarding', auditFallback: true, employee: $employee);
        $fromName = (string) config('mailboxes.onboarding.from_name', config('app.name'));
        $supportEmail = config('mailboxes.activation.support_email')
            ?: $senderResolver->resolve('support')->address
            ?: $sender->address;

        $mailable = new EmployeeOnboardingMail(
            employeeName: $employee->full_name,
            loginEmail: $this->personalEmail,
            activationUrl: $this->activationUrl,
            expiresAtFormatted: \Illuminate\Support\Carbon::parse($this->expiresAt)->format('F j, Y g:i A'),
            supportEmail: (string) $supportEmail,
            fromAddress: (string) $sender->address,
            fromName: $fromName,
            replyToAddress: (string) ($senderResolver->resolve('support')->address ?: $sender->address),
            logoDataUri: $branding->documentsLogoDataUri($employee->company),
            companyName: $employee->company?->name ?? config('app.name'),
        );

        $message = $mail->dispatchMailable([
            'company_id' => (int) $employee->company_id,
            'branch_id' => $employee->branch_id,
            'user_id' => (int) ($employee->created_by ?? 1),
            'to' => [['email' => $this->personalEmail, 'name' => $employee->full_name]],
            'sender_purpose' => 'employee_onboarding',
            'metadata' => [
                'module' => 'hr',
                'entity_type' => 'employee',
                'entity_id' => $employee->id,
            ],
        ], $mailable);

        $audit->logForEmployee(MailboxAuditAction::InvitationSent, $employee, [
            'personal_email' => $this->personalEmail,
            'delivered' => $message !== null,
            'from' => $sender->address,
            'sender_purpose' => 'employee_onboarding',
            'used_fallback' => $sender->usedFallback,
            'email_message_id' => $message?->id,
        ]);
    }
}
