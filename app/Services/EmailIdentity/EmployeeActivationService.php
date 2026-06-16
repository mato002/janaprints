<?php

namespace App\Services\EmailIdentity;

use App\Enums\EmailIdentity\EmployeeActivationStatus;
use App\Enums\EmailIdentity\MailboxAuditAction;
use App\Models\EmailIdentity\EmployeeActivation;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EmployeeActivationService
{
    public function __construct(
        protected EmailIdentityAuditService $audit,
        protected EmployeeActivationRoleResolver $roleResolver,
    ) {}

    /**
     * @return array{activation: EmployeeActivation, plain_token: string, activation_url: string}
     */
    public function createActivation(
        Employee $employee,
        User $user,
        string $personalEmail,
        ?string $intendedRole = null,
    ): array {
        $plainToken = Str::random(64);
        $expiresAt = now()->addHours(config('mailboxes.activation.token_expiry_hours', 72));

        $activation = EmployeeActivation::query()->create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'user_id' => $user->id,
            'personal_email' => strtolower(trim($personalEmail)),
            'intended_role' => $intendedRole,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
        ]);

        return [
            'activation' => $activation,
            'plain_token' => $plainToken,
            'activation_url' => route('employee.activate.show', ['token' => $plainToken]),
        ];
    }

    /**
     * @return array{activation: EmployeeActivation, plain_token: string, activation_url: string}
     */
    public function refreshActivationToken(EmployeeActivation $activation, ?string $intendedRole = null): array
    {
        if ($activation->isActivated()) {
            throw ValidationException::withMessages([
                'activation' => __('This activation link has already been used.'),
            ]);
        }

        $plainToken = Str::random(64);
        $expiresAt = now()->addHours(config('mailboxes.activation.token_expiry_hours', 72));

        $activation->update([
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
            'intended_role' => $intendedRole ?? $activation->intended_role,
        ]);

        return [
            'activation' => $activation->fresh(),
            'plain_token' => $plainToken,
            'activation_url' => route('employee.activate.show', ['token' => $plainToken]),
        ];
    }

    public function findPendingActivation(string $plainToken): ?EmployeeActivation
    {
        $activation = EmployeeActivation::query()
            ->with(['employee.jobTitle', 'employee.department', 'user'])
            ->where('token_hash', hash('sha256', $plainToken))
            ->whereNull('activated_at')
            ->first();

        if (! $activation || $activation->isExpired()) {
            return null;
        }

        return $activation;
    }

    public function activate(EmployeeActivation $activation, string $password): User
    {
        if ($activation->isActivated()) {
            throw ValidationException::withMessages([
                'token' => __('This activation link has already been used.'),
            ]);
        }

        if ($activation->isExpired()) {
            throw ValidationException::withMessages([
                'token' => __('This activation link has expired.'),
            ]);
        }

        return DB::transaction(function () use ($activation, $password) {
            $employee = $activation->employee;
            $user = $activation->user;

            if (! $user) {
                throw ValidationException::withMessages([
                    'token' => __('Unable to complete activation.'),
                ]);
            }

            $user->password = $password;
            $user->is_active = true;
            $user->save();

            if (! $user->hasVerifiedEmail()) {
                $user->markEmailAsVerified();
            }

            $employee->update([
                'activation_status' => EmployeeActivationStatus::Activated,
                'is_active' => true,
            ]);

            $activation->update(['activated_at' => now()]);

            $this->assignResolvedRole($employee, $user, $activation);

            return $user->fresh();
        });
    }

    protected function assignResolvedRole(Employee $employee, User $user, EmployeeActivation $activation): ?string
    {
        $explicitRole = $activation->intended_role ?: $employee->activation_role;
        $resolvedRole = $this->roleResolver->resolve($employee, $explicitRole);

        if ($resolvedRole && $this->roleResolver->roleExists($resolvedRole)) {
            $user->syncRoles([$resolvedRole]);

            $this->audit->logForEmployee(MailboxAuditAction::ActivationCompleted, $employee, [
                'user_id' => $user->id,
                'activation_id' => $activation->id,
                'assigned_role' => $resolvedRole,
            ]);

            return $resolvedRole;
        }

        $this->audit->logForEmployee(MailboxAuditAction::ActivationCompletedWithoutRole, $employee, [
            'user_id' => $user->id,
            'activation_id' => $activation->id,
            'requested_role' => $explicitRole,
            'fallback_role' => config('employee_onboarding.default_role'),
        ]);

        return null;
    }
}
