<?php

namespace App\Mail;

use App\Mail\Concerns\UsesCorporateMailSender;
use App\Models\PublicQuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PublicQuoteRequestConfirmationMail extends Mailable
{
    use Queueable, SerializesModels, UsesCorporateMailSender;

    public function __construct(public PublicQuoteRequest $quoteRequest) {}

    public function envelope(): Envelope
    {
        return $this->corporateEnvelope(
            'storefront_quote',
            'Your Jana Prints Quote Request Has Been Received',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.public.quote-request-confirmation',
            text: 'mail.public.quote-request-confirmation-text',
            with: [
                'quoteRequest' => $this->quoteRequest,
                'contact' => config('conversion.contact'),
                'whatsapp' => config('conversion.whatsapp'),
            ],
        );
    }
}
