<?php

namespace App\Enums;

enum PostingAccountResolver: string
{
    case FixedAccount = 'fixed_account';
    case AccountKey = 'account_key';
    case ContextAccount = 'context_account';

    public function label(): string
    {
        return match ($this) {
            self::FixedAccount => __('Fixed GL account'),
            self::AccountKey => __('Mapped account key'),
            self::ContextAccount => __('Context account field'),
        };
    }
}
