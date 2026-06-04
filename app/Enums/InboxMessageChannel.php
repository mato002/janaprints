<?php

namespace App\Enums;

enum InboxMessageChannel: string
{
    case WhatsApp = 'whatsapp';
    case Sms = 'sms';
    case Email = 'email';
    case InApp = 'in_app';
    case Manual = 'manual';
    case PhoneNote = 'phone_note';

    public function label(): string
    {
        return match ($this) {
            self::WhatsApp => __('WhatsApp'),
            self::Sms => __('SMS'),
            self::Email => __('Email'),
            self::InApp => __('In-app'),
            self::Manual => __('Manual'),
            self::PhoneNote => __('Phone call note'),
        };
    }

    public function toLogChannel(): CommunicationLogChannel
    {
        return match ($this) {
            self::WhatsApp => CommunicationLogChannel::WhatsApp,
            self::Sms => CommunicationLogChannel::Sms,
            self::Email => CommunicationLogChannel::Email,
            self::InApp, self::Manual, self::PhoneNote => CommunicationLogChannel::System,
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::WhatsApp => 'chat-bubble-left-right',
            self::Sms => 'device-phone-mobile',
            self::Email => 'envelope',
            self::PhoneNote => 'phone',
            self::Manual => 'pencil-square',
            self::InApp => 'inbox',
        };
    }

    public function bubbleClass(): string
    {
        return match ($this) {
            self::WhatsApp => 'ring-emerald-200',
            self::Sms => 'ring-sky-200',
            self::Email => 'ring-indigo-200',
            self::PhoneNote => 'ring-violet-200',
            default => 'ring-slate-200',
        };
    }
}
