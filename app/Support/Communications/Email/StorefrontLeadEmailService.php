<?php

namespace App\Support\Communications\Email;

use App\Mail\PublicContactMessageConfirmationMail;
use App\Mail\PublicContactMessageInternalNotificationMail;
use App\Mail\PublicQuoteRequestConfirmationMail;
use App\Mail\PublicQuoteRequestInternalNotificationMail;
use App\Models\PublicContactMessage;
use App\Models\PublicQuoteRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorefrontLeadEmailService
{
    public function __construct(
        protected CorporateMailDispatcher $mail,
        protected StorefrontTenantResolver $tenant,
    ) {}

    public function dispatchQuoteRequestEmails(PublicQuoteRequest $quoteRequest, ?string $artworkPath = null): void
    {
        [$company, $branch] = $this->tenant->resolve();

        if (! $company) {
            Log::warning('Storefront quote request email skipped: tenant not resolved.', [
                'quote_request_id' => $quoteRequest->id,
            ]);

            return;
        }

        $userId = $this->tenant->systemUserId($company);

        $this->mail->dispatchMailable([
            'company_id' => $company->id,
            'branch_id' => $branch?->id,
            'user_id' => $userId,
            'to' => [['email' => $quoteRequest->email, 'name' => $quoteRequest->name]],
            'sender_purpose' => 'storefront_quote',
            'metadata' => [
                'module' => 'website',
                'entity_type' => 'public_quote_request',
                'entity_id' => $quoteRequest->id,
            ],
        ], new PublicQuoteRequestConfirmationMail($quoteRequest));

        $adminEmail = config('leads.admin_email');

        if (! filled($adminEmail)) {
            return;
        }

        $internal = new PublicQuoteRequestInternalNotificationMail($quoteRequest);
        $attachments = [];

        if ($artworkPath !== null && is_file($artworkPath)) {
            $attachments[] = [
                'attachment_type' => 'document',
                'label' => $quoteRequest->artwork_original_name ?? basename($artworkPath),
                'file_path' => $this->copyArtworkToMailStorage($artworkPath),
            ];
        }

        $this->mail->dispatch(array_merge([
            'company_id' => $company->id,
            'branch_id' => $branch?->id,
            'user_id' => $userId,
            'to' => [['email' => (string) $adminEmail]],
            'subject' => $internal->envelope()->subject,
            'body' => $internal->render(),
            'sender_purpose' => 'storefront_quote',
            'attachments' => $attachments,
            'metadata' => [
                'module' => 'website',
                'entity_type' => 'public_quote_request',
                'entity_id' => $quoteRequest->id,
                'audience' => 'internal',
            ],
        ]));
    }

    public function dispatchContactMessageEmails(PublicContactMessage $contactMessage): void
    {
        [$company, $branch] = $this->tenant->resolve();

        if (! $company) {
            Log::warning('Storefront contact message email skipped: tenant not resolved.', [
                'contact_message_id' => $contactMessage->id,
            ]);

            return;
        }

        $userId = $this->tenant->systemUserId($company);

        $this->mail->dispatchMailable([
            'company_id' => $company->id,
            'branch_id' => $branch?->id,
            'user_id' => $userId,
            'to' => [['email' => $contactMessage->email, 'name' => $contactMessage->name]],
            'sender_purpose' => 'storefront_contact',
            'metadata' => [
                'module' => 'website',
                'entity_type' => 'public_contact_message',
                'entity_id' => $contactMessage->id,
            ],
        ], new PublicContactMessageConfirmationMail($contactMessage));

        $adminEmail = config('leads.admin_email');

        if (! filled($adminEmail)) {
            return;
        }

        $this->mail->dispatchMailable([
            'company_id' => $company->id,
            'branch_id' => $branch?->id,
            'user_id' => $userId,
            'to' => [['email' => (string) $adminEmail]],
            'sender_purpose' => 'storefront_contact',
            'metadata' => [
                'module' => 'website',
                'entity_type' => 'public_contact_message',
                'entity_id' => $contactMessage->id,
                'audience' => 'internal',
            ],
        ], new PublicContactMessageInternalNotificationMail($contactMessage));
    }

    protected function copyArtworkToMailStorage(string $absolutePath): string
    {
        $disk = (string) config('communications.email_attachment_disk', 'local');
        $destination = 'email-attachments/'.now()->format('Y/m').'/'.basename($absolutePath);

        \Illuminate\Support\Facades\Storage::disk($disk)->put(
            $destination,
            file_get_contents($absolutePath) ?: '',
        );

        return $destination;
    }
}
