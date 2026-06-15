<?php

namespace App\Services\Storefront;

use App\Mail\PublicQuoteRequestConfirmationMail;
use App\Mail\PublicQuoteRequestInternalNotificationMail;
use App\Models\PublicQuoteRequest;
use App\Services\Commercial\PublicQuoteRequestNotificationService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PublicQuoteRequestService
{
    public function __construct(
        protected ?PublicQuoteRequestNotificationService $notifications = null,
    ) {
        $this->notifications ??= app(PublicQuoteRequestNotificationService::class);
    }

    public function store(array $data, ?UploadedFile $artwork = null): PublicQuoteRequest
    {
        $artworkPath = null;
        $artworkOriginalName = null;

        if ($artwork) {
            [$artworkPath, $artworkOriginalName] = $this->storeArtwork($artwork);
        }

        $quoteRequest = PublicQuoteRequest::query()->create([
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'phone' => $data['phone'],
            'email' => $data['email'],
            'service_needed' => $data['service_needed'],
            'quantity' => $data['quantity'] ?? null,
            'deadline' => $data['deadline'] ?? null,
            'message' => $data['message'],
            'artwork_path' => $artworkPath,
            'artwork_original_name' => $artworkOriginalName,
            'source' => 'storefront',
        ]);

        $this->dispatchEmails($quoteRequest);
        $this->notifications->notifyNewRequest($quoteRequest);

        return $quoteRequest;
    }

    /**
     * @return array{0: string, 1: string}
     */
    protected function storeArtwork(UploadedFile $file): array
    {
        $disk = config('leads.artwork.disk', 'public');
        $directory = config('leads.artwork.directory', 'quote-artwork');
        $subdir = now()->format('Y/m');
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid().'.'.$extension;

        $path = $file->storeAs("{$directory}/{$subdir}", $filename, $disk);

        return [$path, $file->getClientOriginalName()];
    }

    protected function dispatchEmails(PublicQuoteRequest $quoteRequest): void
    {
        $mailer = Mail::mailer((string) config('leads.mailer', config('mail.default')));

        try {
            $mailer->to($quoteRequest->email)->send(new PublicQuoteRequestConfirmationMail($quoteRequest));
        } catch (\Throwable $e) {
            Log::warning('Failed to send quote request confirmation email.', [
                'quote_request_id' => $quoteRequest->id,
                'error' => $e->getMessage(),
            ]);
        }

        $adminEmail = config('leads.admin_email');

        if (! $adminEmail) {
            return;
        }

        try {
            $mailer->to($adminEmail)->send(new PublicQuoteRequestInternalNotificationMail($quoteRequest));
        } catch (\Throwable $e) {
            Log::warning('Failed to send quote request internal notification.', [
                'quote_request_id' => $quoteRequest->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function artworkDownloadPath(PublicQuoteRequest $quoteRequest): ?string
    {
        if (! $quoteRequest->artwork_path) {
            return null;
        }

        $disk = config('leads.artwork.disk', 'public');

        if (! Storage::disk($disk)->exists($quoteRequest->artwork_path)) {
            return null;
        }

        return Storage::disk($disk)->path($quoteRequest->artwork_path);
    }
}
