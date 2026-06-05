<?php

namespace App\Enums;

enum LoginAttemptFailureReason: string
{
    case InvalidCredentials = 'invalid_credentials';
    case InactiveAccount = 'inactive_account';
    case LockedOut = 'locked_out';

    public function label(): string
    {
        return match ($this) {
            self::InvalidCredentials => __('Invalid credentials'),
            self::InactiveAccount => __('Inactive account'),
            self::LockedOut => __('Account locked'),
        };
    }
}
