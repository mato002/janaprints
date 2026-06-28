<?php

namespace App\Enums;

enum ProductionSessionWasteReason: string
{
    case SetupWaste = 'setup_waste';
    case PaperJam = 'paper_jam';
    case Misalignment = 'misalignment';
    case InkIssue = 'ink_issue';
    case OperatorError = 'operator_error';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SetupWaste => __('Setup Waste'),
            self::PaperJam => __('Paper Jam'),
            self::Misalignment => __('Misalignment'),
            self::InkIssue => __('Ink Issue'),
            self::OperatorError => __('Operator Error'),
            self::Other => __('Other'),
        };
    }
}
