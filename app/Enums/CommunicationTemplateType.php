<?php

namespace App\Enums;

enum CommunicationTemplateType: string
{
    case Transactional = 'transactional';
    case Operational = 'operational';
    case System = 'system';
    case Marketing = 'marketing';

    public function label(): string
    {
        return match ($this) {
            self::Transactional => __('Transactional'),
            self::Operational => __('Operational'),
            self::System => __('System'),
            self::Marketing => __('Marketing'),
        };
    }
}
