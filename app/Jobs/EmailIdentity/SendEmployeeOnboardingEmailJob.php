<?php

namespace App\Jobs\EmailIdentity;

use App\Jobs\PlatformJob;
use App\Mail\EmployeeOnboardingMail;
use App\Models\Employee;
use App\Services\EmailIdentity\EmailIdentityAuditService;
use App\Services\EmailIdentity\EmailSenderResolver;
use App\Enums\EmailIdentity\MailboxAuditAction;
use Illuminate\Support\Facades\Mail;

class SendEmployeeOnboardingEmailJob extends PlatformJob
{
    public function __construct(
        public int $employeeId,
        public string $personalEmail,
        public string $corporateEmail,
        public string $activationUrl,
        public string $expiresAt,
    ) {
        parent::__construct();
        $this->useQueue('emails');
    }

    public function handle(EmailIdentityAuditService $audit, EmailSenderResolver $senderResolver): void
    {
        $employee = Employee::query()->find($this->employeeId);

        if (! $employee) {
            return;
        }

        $sender = $senderResolver->resolveOrAbort('employee_onboarding', auditFallback: true, employee: $employee);
        $fromName = (string) config('mailboxes.onboarding.from_name', config('app.name'));
        $supportEmail = config('mailboxes.activation.support_email')
            ?: $senderResolver->resolve('support')->address
            ?: $sender->address;

        $mailer = (string) config('mailboxes.onboarding.mailer', 'onboarding');

        if (! filled(config('mail.mailers.'.$mailer.'.username')) && ! filled(config('mail.mailers.'.$mailer.'.host'))) {
            $audit->logForEmployee(MailboxAuditAction::SenderFallbackUsed, $employee, [
                'reason' => 'onboarding_mailer_not_configured',
                'mailer' => $mailer,
            ]);
        }

        Mail::mailer($mailer)->to($this->personalEmail)->send(new EmployeeOnboardingMail(
            employeeName: $employee->full_name,
            corporateEmail: $this->corporateEmail,
            activationUrl: $this->activationUrl,
            expiresAtFormatted: \Illuminate\Support\Carbon::parse($this->expiresAt)->format('F j, Y g:i A'),
            supportEmail: (string) $supportEmail,
            fromAddress: (string) $sender->address,
            fromName: $fromName,
            replyToAddress: (string) ($senderResolver->resolve('support')->address ?: $sender->address),
        ));

        $audit->logForEmployee(MailboxAuditAction::InvitationSent, $employee, [
            'personal_email' => $this->personalEmail,
            'delivered' => true,
            'from' => $sender->address,
            'mailer' => $mailer,
            'sender_purpose' => 'employee_onboarding',
            'used_fallback' => $sender->usedFallback,
        ]);
    }
}
