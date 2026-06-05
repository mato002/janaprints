<?php

namespace App\Enums;

enum BackgroundJobType: string
{
    case Email = 'email';
    case Sms = 'sms';
    case Notification = 'notification';
    case Report = 'report';
    case Export = 'export';
    case Import = 'import';
    case Accounting = 'accounting';
    case General = 'general';

    public function label(): string
    {
        return match ($this) {
            self::Email => __('Email Jobs'),
            self::Sms => __('SMS Jobs'),
            self::Notification => __('Notification Jobs'),
            self::Report => __('Report Jobs'),
            self::Export => __('Export Jobs'),
            self::Import => __('Import Jobs'),
            self::Accounting => __('Accounting Jobs'),
            self::General => __('General Jobs'),
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Email => __('Email'),
            self::Sms => __('SMS'),
            self::Notification => __('Notification'),
            self::Report => __('Report'),
            self::Export => __('Export'),
            self::Import => __('Import'),
            self::Accounting => __('Accounting'),
            self::General => __('General'),
        };
    }
}
