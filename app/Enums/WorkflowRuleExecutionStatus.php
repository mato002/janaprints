<?php

namespace App\Enums;

enum WorkflowRuleExecutionStatus: string
{
    case Completed = 'completed';
    case Failed = 'failed';
    case Skipped = 'skipped';

    public function label(): string
    {
        return match ($this) {
            self::Completed => __('Completed'),
            self::Failed => __('Failed'),
            self::Skipped => __('Skipped'),
        };
    }
}
