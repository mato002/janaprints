<?php

namespace App\Mail;

use App\Models\PublicQuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PublicQuoteRequestInternalNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PublicQuoteRequest $quoteRequest) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Quote Request Received — '.$this->quoteRequest->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.public.quote-request-internal',
            text: 'mail.public.quote-request-internal-text',
            with: [
                'quoteRequest' => $this->quoteRequest,
                'adminUrl' => route('admin.public-quote-requests.show', $this->quoteRequest),
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->quoteRequest->artwork_path) {
            return [];
        }

        $disk = config('leads.artwork.disk', 'public');

        if (! Storage::disk($disk)->exists($this->quoteRequest->artwork_path)) {
            return [];
        }

        return [
            Attachment::fromStorageDisk($disk, $this->quoteRequest->artwork_path)
                ->as($this->quoteRequest->artwork_original_name ?? 'artwork')
                ->withMime(Storage::disk($disk)->mimeType($this->quoteRequest->artwork_path) ?? 'application/octet-stream'),
        ];
    }
}
