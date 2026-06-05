<?php

namespace App\Enums;

enum TrainingType: string
{
    case Internal = 'internal';
    case External = 'external';
    case Certification = 'certification';
    case Compliance = 'compliance';
    case Safety = 'safety';
    case Technical = 'technical';

    public function label(): string
    {
        return match ($this) {
            self::Internal => __('Internal'),
            self::External => __('External'),
            self::Certification => __('Certification'),
            self::Compliance => __('Compliance'),
            self::Safety => __('Safety'),
            self::Technical => __('Technical'),
        };
    }
}
