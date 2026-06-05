<?php

namespace App\Enums;

enum IntegrationEmailProvider: string
{

    case Smtp = 'smtp';
    case Mailgun = 'mailgun';
    case Sendgrid = 'sendgrid';
    case Ses = 'ses';
    case CustomSmtp = 'custom_smtp';

    public function label(): string
    {
        return match ($this) {
            self::Smtp => __('SMTP'),
            self::Mailgun => __('Mailgun'),
            self::Sendgrid => __('SendGrid'),
            self::Ses => __('Amazon SES'),
            self::CustomSmtp => __('Custom SMTP'),
        };
    }
}
