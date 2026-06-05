<?php

namespace App\Enums;

enum ApprovalChainMode: string
{
    case Sequential = 'sequential';
    case Parallel = 'parallel';
    case Conditional = 'conditional';

    public function label(): string
    {
        return match ($this) {
            self::Sequential => __('Sequential'),
            self::Parallel => __('Parallel'),
            self::Conditional => __('Conditional'),
        };
    }
}
