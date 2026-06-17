<?php

namespace App\Services\EmailIdentity;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Jobs\EmailIdentity\SendEmployeeOnboardingEmailJob;
use App\Models\EmailIdentity\EmployeeActivation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeOnboardingService
{
    public function __construct(
        protected EmployeeActivationService $activationService,
        protected EmployeeActivationManagementService $activationManagement,
        protected EmailIdentityAuditService $audit,
        protected EmployeeOnboardingSmsNotifier $smsNotifier,
    ) {}

    public function ensureOnboarded(Employee $employee, string $personalEmail, ?string $intendedRole = null): Employee
    {
        $personalEmail = strtolower(trim($personalEmail));

        if (! filled($personalEmail)) {
            throw ValidationException::withMessages([
                'email' => __('Personal email is required for employee onboarding.'),
            ]);
        }

        if ($intendedRole) {
            $employee->update(['activation_role' => $intendedRole]);
            $employee = $employee->fresh();
        }

        if ($employee->activation_status === EmployeeActivationStatus::Activated && $employee->user) {
            return $employee;
        }

        if ($employee->user || $this->activationManagement->latestOpenActivation($employee)) {
            return $this->reconcileExistingIdentity($employee, $personalEmail);
        }

        return $this->onboardAfterCreate($employee->fresh(), $personalEmail, $employee->activation_role);
    }

    public function onboardAfterCreate(Employee $employee, string $personalEmail, ?string $intendedRole = null): Employee
    {
        $personalEmail = strtolower(trim($personalEmail));

        if ($intendedRole) {
            $employee->update(['activation_role' => $intendedRole]);
        }

        $payload = DB::transaction(function () use ($employee, $personalEmail) {
            $employee->update([
                'email' => $personalEmail,
                'activation_status' => EmployeeActivationStatus::PendingActivation,
                'is_active' => false,
            ]);

            $user = $employee->user;

            if (! $user) {
                $user = User::query()->where('email', $personalEmail)->first();

                if ($user !== null) {
                    if ($user->employee_id !== null && $user->employee_id !== $employee->id) {
                        throw ValidationException::withMessages([
                            'email' => __('This email is already linked to another employee account.'),
                        ]);
                    }

                    $user->update([
                        'company_id' => $employee->company_id,
                        'default_branch_id' => $employee->branch_id,
                        'employee_id' => $employee->id,
                        'name' => $employee->full_name,
                        'email' => $personalEmail,
                        'is_active' => false,
                    ]);
                } else {
                    $user = User::query()->create([
                        'company_id' => $employee->company_id,
                        'default_branch_id' => $employee->branch_id,
                        'employee_id' => $employee->id,
                        'name' => $employee->full_name,
                        'email' => $personalEmail,
                        'password' => Str::password(32),
                        'is_active' => false,
                    ]);
                }
            } else {
                $user->update([
                    'email' => $personalEmail,
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
                    $employee->activation_role,
                );

            return [
                'employee' => $employee->fresh(),
                'personal_email' => $personalEmail,
                'activation_url' => $activationPayload['activation_url'],
                'expires_at' => $activationPayload['activation']->expires_at,
                'activation' => $activationPayload['activation'],
            ];
        });

        $this->dispatchOnboardingNotifications(
            $payload['employee'],
            $payload['personal_email'],
            $payload['activation_url'],
            $payload['expires_at'],
            $payload['activation'],
        );

        return $payload['employee']->fresh();
    }

    protected function reconcileExistingIdentity(Employee $employee, string $personalEmail): Employee
    {
        $personalEmail = strtolower(trim($personalEmail));

        if ($employee->email !== $personalEmail) {
            $employee->update(['email' => $personalEmail]);
            $employee->user?->update(['email' => $personalEmail]);
        }

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
        string $activationUrl,
        \DateTimeInterface $expiresAt,
        ?EmployeeActivation $activation = null,
    ): void {
        try {
            SendEmployeeOnboardingEmailJob::dispatch(
                employeeId: $employee->id,
                personalEmail: $personalEmail,
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
