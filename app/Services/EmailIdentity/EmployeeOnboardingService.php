<?php

namespace App\Services\EmailIdentity;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Jobs\EmailIdentity\SendEmployeeOnboardingEmailJob;
use App\Models\EmailIdentity\CorporateMailbox;
use App\Models\EmailIdentity\EmployeeActivation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeOnboardingService
{
    public function __construct(
        protected CorporateMailboxProvisioningService $mailboxProvisioning,
        protected EmployeeActivationService $activationService,
        protected EmployeeActivationManagementService $activationManagement,
        protected EmailIdentityAuditService $audit,
        protected EmployeeOnboardingSmsNotifier $smsNotifier,
    ) {}

    public function ensureOnboarded(Employee $employee, string $personalEmail, ?string $intendedRole = null): Employee
    {
        if (! filled($personalEmail)) {
            throw ValidationException::withMessages([
                'email' => __('Personal email is required for employee onboarding.'),
            ]);
        }

        if ($intendedRole) {
            $employee->update(['activation_role' => $intendedRole]);
            $employee = $employee->fresh();
        }

        if ($employee->activation_status === EmployeeActivationStatus::Activated && filled($employee->corporate_email)) {
            return $employee;
        }

        if (filled($employee->corporate_email)) {
            return $this->reconcileExistingIdentity($employee, $personalEmail);
        }

        return $this->onboardAfterCreate($employee->fresh(), $personalEmail, $employee->activation_role);
    }

    public function onboardAfterCreate(Employee $employee, string $personalEmail, ?string $intendedRole = null): Employee
    {
        if ($intendedRole) {
            $employee->update(['activation_role' => $intendedRole]);
        }

        $payload = DB::transaction(function () use ($employee, $personalEmail) {
            $corporateEmail = $this->mailboxProvisioning->generateForEmployee($employee);

            $employee->update([
                'corporate_email' => $corporateEmail,
                'activation_status' => EmployeeActivationStatus::PendingActivation,
                'is_active' => false,
            ]);

            if (! CorporateMailbox::query()->where('employee_id', $employee->id)->exists()) {
                $this->mailboxProvisioning->provisionForEmployee($employee->fresh(), $corporateEmail);
            }

            $user = $employee->user;

            if (! $user) {
                $user = User::query()->create([
                    'company_id' => $employee->company_id,
                    'default_branch_id' => $employee->branch_id,
                    'employee_id' => $employee->id,
                    'name' => $employee->full_name,
                    'email' => $corporateEmail,
                    'password' => Str::password(32),
                    'is_active' => false,
                ]);
            }

            $existingActivation = $this->activationManagement->latestOpenActivation($employee->fresh());

            $activationPayload = $existingActivation
                ? $this->activationService->refreshActivationToken($existingActivation, $employee->activation_role)
                : $this->activationService->createActivation(
                    $employee->fresh(),
                    $user,
                    $personalEmail,
                    $corporateEmail,
                    $employee->activation_role,
                );

            return [
                'employee' => $employee->fresh(),
                'personal_email' => $personalEmail,
                'corporate_email' => $corporateEmail,
                'activation_url' => $activationPayload['activation_url'],
                'expires_at' => $activationPayload['activation']->expires_at,
                'activation' => $activationPayload['activation'],
            ];
        });

        $this->dispatchOnboardingNotifications(
            $payload['employee'],
            $payload['personal_email'],
            $payload['corporate_email'],
            $payload['activation_url'],
            $payload['expires_at'],
            $payload['activation'],
        );

        return $payload['employee']->fresh();
    }

    protected function reconcileExistingIdentity(Employee $employee, string $personalEmail): Employee
    {
        $openActivation = $this->activationManagement->latestOpenActivation($employee);

        if ($openActivation && $openActivation->personal_email !== $personalEmail) {
            $openActivation->update(['personal_email' => $personalEmail]);
        }

        try {
            if ($openActivation && ! $openActivation->isExpired()) {
                $this->activationManagement->resendInvitation($employee);
            } else {
                $this->activationManagement->regenerateActivation($employee);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return $employee->fresh();
    }

    protected function dispatchOnboardingNotifications(
        Employee $employee,
        string $personalEmail,
        string $corporateEmail,
        string $activationUrl,
        \DateTimeInterface $expiresAt,
        ?EmployeeActivation $activation = null,
    ): void {
        try {
            SendEmployeeOnboardingEmailJob::dispatch(
                employeeId: $employee->id,
                personalEmail: $personalEmail,
                corporateEmail: $corporateEmail,
                activationUrl: $activationUrl,
                expiresAt: $expiresAt->format('c'),
            );

            $activation?->update(['last_invitation_sent_at' => now()]);

            $this->audit->logForEmployee(MailboxAuditAction::InvitationSent, $employee, [
                'personal_email' => $personalEmail,
                'queued' => true,
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            $this->audit->logForEmployee(MailboxAuditAction::InvitationSent, $employee, [
                'personal_email' => $personalEmail,
                'queued' => false,
                'error' => $exception->getMessage(),
            ]);
        }

        $this->smsNotifier->notifyIfConfigured($employee);
    }
}
