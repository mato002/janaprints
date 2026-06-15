<?php

namespace App\Mail\Concerns;

use App\Services\EmailIdentity\EmailSenderResolver;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Envelope;

trait UsesCorporateMailSender
{
    protected function corporateEnvelope(string $purpose, string $subject): Envelope
    {
        $sender = app(EmailSenderResolver::class)->resolveOrAbort($purpose);
        $name = (string) config('leads.from_name', config('mail.from.name'));

        $replyTo = (string) (
            config('leads.reply_to')
            ?: config('mailboxes.department.info')
            ?: $sender->address
        );

        return new Envelope(
            from: new Address((string) $sender->address, $name),
            replyTo: [new Address($replyTo, $name)],
            subject: $subject,
        );
    }
}
