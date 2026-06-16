<?php

namespace App\Services\EmailIdentity;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Jobs\EmailIdentity\SendEmployeeOnboardingEmailJob;
use App\Models\EmailIdentity\EmployeeActivation;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeActivationManagementService
{
    public function __construct(
        protected EmployeeActivationService $activationService,
        protected EmailIdentityAuditService $audit,
    ) {}

    public function resendInvitation(Employee $employee): EmployeeActivation
    {
        $this->assertCanManageActivation($employee);

        $activation = $this->latestOpenActivation($employee);

        if (! $activation) {
            throw ValidationException::withMessages([
                'activation' => __('No pending activation exists for this employee.'),
            ]);
        }

        if ($activation->isActivated()) {
            throw ValidationException::withMessages([
                'activation' => __('This employee has already activated their account.'),
            ]);
        }

        if ($activation->isExpired()) {
            throw ValidationException::withMessages([
                'activation' => __('Activation link has expired. Regenerate a new link instead.'),
            ]);
        }

        $payload = $this->activationService->refreshActivationToken($activation);

        $this->queueInvitation(
            $employee,
            $payload['activation'],
            $payload['activation_url'],
        );

        $this->audit->logForEmployee(MailboxAuditAction::InvitationResent, $employee, [
            'activation_id' => $activation->id,
            'personal_email' => $activation->personal_email,
        ]);

        return $payload['activation'];
    }

    public function regenerateActivation(Employee $employee): EmployeeActivation
    {
        $this->assertCanManageActivation($employee);

        if ($employee->activation_status === EmployeeActivationStatus::Activated) {
            throw ValidationException::withMessages([
                'activation' => __('Activated employees cannot receive a new activation link without admin reset.'),
            ]);
        }

        $user = $employee->user;

        if (! $user) {
            throw ValidationException::withMessages([
                'activation' => __('No linked user account exists for this employee.'),
            ]);
        }

        $personalEmail = strtolower(trim((string) ($employee->email ?: $this->latestOpenActivation($employee)?->personal_email)));

        if (! filled($personalEmail)) {
            throw ValidationException::withMessages([
                'email' => __('Personal email is required to send an activation invitation.'),
            ]);
        }

        return DB::transaction(function () use ($employee, $user, $personalEmail) {
            $activation = $this->latestOpenActivation($employee);

            $payload = $activation
                ? $this->activationService->refreshActivationToken($activation, $employee->activation_role)
                : $this->activationService->createActivation(
                    $employee,
                    $user,
                    $personalEmail,
                    $employee->activation_role,
                );

            $this->queueInvitation(
                $employee,
                $payload['activation'],
                $payload['activation_url'],
            );

            $employee->update([
                'email' => $personalEmail,
                'activation_status' => EmployeeActivationStatus::PendingActivation,
                'is_active' => false,
            ]);

            $user->update([
                'email' => $personalEmail,
                'is_active' => false,
            ]);

            $this->audit->logForEmployee(MailboxAuditAction::ActivationRegenerated, $employee, [
                'activation_id' => $payload['activation']->id,
                'personal_email' => $personalEmail,
            ]);

            return $payload['activation'];
        });
    }

    public function latestOpenActivation(Employee $employee): ?EmployeeActivation
    {
        return EmployeeActivation::query()
            ->where('employee_id', $employee->id)
            ->whereNull('activated_at')
            ->latest('id')
            ->first();
    }

    public function activationDisplayStatus(Employee $employee): string
    {
        if ($employee->activation_status === EmployeeActivationStatus::Activated) {
            return 'activated';
        }

        $activation = $this->latestOpenActivation($employee);

        if (! $activation) {
            return filled($employee->email) && $employee->user ? 'pending' : 'none';
        }

        return $activation->isExpired() ? 'expired' : 'pending';
    }

    protected function assertCanManageActivation(Employee $employee): void
    {
        if (! filled($employee->email)) {
            throw ValidationException::withMessages([
                'employee' => __('Employee personal email is required for activation.'),
            ]);
        }
    }

    protected function queueInvitation(Employee $employee, EmployeeActivation $activation, string $activationUrl): void
    {
        SendEmployeeOnboardingEmailJob::dispatch(
            employeeId: $employee->id,
            personalEmail: $activation->personal_email,
            activationUrl: $activationUrl,
            expiresAt: $activation->expires_at->format('c'),
        );

        $activation->update(['last_invitation_sent_at' => now()]);
    }
}
