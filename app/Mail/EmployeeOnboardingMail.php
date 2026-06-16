<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeOnboardingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $employeeName,
        public string $loginEmail,
        public string $activationUrl,
        public string $expiresAtFormatted,
        public string $supportEmail,
        public string $fromAddress,
        public string $fromName,
        public string $replyToAddress,
        public ?string $logoDataUri = null,
        public string $companyName = 'Jana Prints',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address($this->fromAddress, $this->fromName),
            replyTo: [
                new Address($this->replyToAddress, $this->fromName),
            ],
            subject: __('Welcome to :company', ['company' => $this->companyName]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.employee-onboarding',
            text: 'mail.employee-onboarding-text',
            with: [
                'logoDataUri' => $this->logoDataUri,
                'companyName' => $this->companyName,
            ],
        );
    }
}
