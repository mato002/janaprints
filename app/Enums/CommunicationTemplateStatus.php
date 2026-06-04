<?php

namespace App\Enums;

enum CommunicationTemplateStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';

    public function label(): string
    {
        return match ($this) {
            self::Active => __('Active'),
            self::Inactive => __('Inactive'),
            self::Draft => __('Draft'),
        };
    }
}
