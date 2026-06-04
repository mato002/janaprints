<?php

namespace App\Enums;

enum CommunicationLogType: string
{
    case Transactional = 'transactional';
    case Operational = 'operational';
    case Approval = 'approval';
    case Reminder = 'reminder';
    case Marketing = 'marketing';
    case System = 'system';
    case Alert = 'alert';
    case Broadcast = 'broadcast';

    public function label(): string
    {
        return match ($this) {
            self::Transactional => __('Transactional'),
            self::Operational => __('Operational'),
            self::Approval => __('Approval'),
            self::Reminder => __('Reminder'),
            self::Marketing => __('Marketing'),
            self::System => __('System'),
            self::Alert => __('Alert'),
            self::Broadcast => __('Broadcast'),
        };
    }
}
