<?php

namespace App\Enums;

enum BankStatementStatus: string
{
    case Draft = 'draft';
    case InProgress = 'in_progress';
    case Reconciled = 'reconciled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::InProgress => __('In progress'),
            self::Reconciled => __('Reconciled'),
        };
    }
}
