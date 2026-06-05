<?php

namespace App\Enums;

enum UserSessionStatus: string
{
    case Active = 'active';
    case Expired = 'expired';
    case LoggedOut = 'logged_out';
    case Revoked = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Expired => __('Expired'),
            self::LoggedOut => __('Logged Out'),
            self::Revoked => __('Revoked'),
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Expired => 'neutral',
            self::LoggedOut => 'draft',
            self::Revoked => 'danger',
        };
    }
}
