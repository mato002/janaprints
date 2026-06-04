<?php

namespace App\Enums;

enum EmailProvider: string
{
    case Unconfigured = 'unconfigured';
    case Smtp = 'smtp';
    case SendGrid = 'sendgrid';
    case Mailgun = 'mailgun';
    case Ses = 'ses';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Unconfigured => __('Not configured'),
            self::Smtp => __('SMTP'),
            self::SendGrid => __('SendGrid'),
            self::Mailgun => __('Mailgun'),
            self::Ses => __('Amazon SES'),
            self::Custom => __('Custom provider'),
        };
    }
}
