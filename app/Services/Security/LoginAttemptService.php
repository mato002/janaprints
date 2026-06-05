<?php

namespace App\Services\Security;

use App\Enums\LoginAttemptFailureReason;
use App\Models\LoginAttempt;
use App\Models\User;
use App\Support\Security\UserAgentParser;
use Illuminate\Http\Request;

class LoginAttemptService
{
    public function __construct(
        protected UserAgentParser $userAgentParser,
    ) {}

    public function record(
        string $email,
        LoginAttemptFailureReason $reason,
        ?User $user = null,
        ?Request $request = null,
    ): LoginAttempt {
        $request ??= request();
        $parsed = $this->userAgentParser->parse($request->userAgent());

        $attempt = LoginAttempt::query()->create([
            'email' => $email,
            'user_id' => $user?->getKey(),
            'company_id' => $user?->company_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device' => $parsed['device'],
            'browser' => $parsed['browser'],
            'platform' => $parsed['platform'],
            'failure_reason' => $reason,
            'attempted_at' => now(),
        ]);

        app(SecurityAuditService::class)->record(
            action: $reason === LoginAttemptFailureReason::LockedOut ? 'locked_out' : 'failed_login',
            subject: $user,
            userId: $user?->getKey(),
            description: $reason === LoginAttemptFailureReason::LockedOut
                ? __('Account lockout triggered for :email', ['email' => $email])
                : __('Failed login attempt for :email', ['email' => $email]),
            metadata: ['email' => $email, 'failure_reason' => $reason->value],
            module: 'authentication',
            entity: 'login_attempt',
        );

        return $attempt;
    }
}
