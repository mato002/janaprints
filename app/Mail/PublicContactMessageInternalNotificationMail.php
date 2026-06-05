<?php

namespace App\Mail;

use App\Models\PublicContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PublicContactMessageInternalNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PublicContactMessage $contactMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Contact Message — '.$this->contactMessage->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.public.contact-message-internal',
            text: 'mail.public.contact-message-internal-text',
            with: [
                'contactMessage' => $this->contactMessage,
                'adminUrl' => route('admin.public-contact-messages.show', $this->contactMessage),
            ],
        );
    }
}
