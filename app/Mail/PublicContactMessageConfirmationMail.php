<?php

namespace App\Mail;

use App\Models\PublicContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PublicContactMessageConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PublicContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We Have Received Your Message — Jana Prints',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.public.contact-message-confirmation',
            text: 'mail.public.contact-message-confirmation-text',
            with: [
                'contactMessage' => $this->contactMessage,
                'contact' => config('conversion.contact'),
                'whatsapp' => config('conversion.whatsapp'),
            ],
        );
    }
}
