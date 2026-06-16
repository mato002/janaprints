<?php

namespace App\Mail\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $resetUrl,
        public string $userName,
        public string $portalLabel,
        public string $companyName,
        public ?string $logoDataUri,
        public int $expireMinutes,
        public string $fromAddress,
        public string $fromName,
        public string $replyToAddress,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            replyTo: [
                new Address($this->replyToAddress, $this->fromName),
            ],
            subject: __('Reset your :portal password', ['portal' => $this->portalLabel]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.auth.reset-password',
            with: [
                'url' => $this->resetUrl,
                'userName' => $this->userName,
                'portalLabel' => $this->portalLabel,
                'companyName' => $this->companyName,
                'logoDataUri' => $this->logoDataUri,
                'expireMinutes' => $this->expireMinutes,
            ],
        );
    }
}
