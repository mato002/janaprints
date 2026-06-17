<?php

namespace App\Services\Storefront;

use App\Models\PublicContactMessage;
use App\Support\Communications\Email\StorefrontLeadEmailService;

class PublicContactMessageService
{
    public function __construct(
        protected ?StorefrontLeadEmailService $leadEmails = null,
    ) {
        $this->leadEmails ??= app(StorefrontLeadEmailService::class);
    }

    public function store(array $data): PublicContactMessage
    {
        $message = PublicContactMessage::query()->create([
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'subject' => $data['subject'],
            'message' => $data['message'],
            'source' => 'storefront',
        ]);

        $this->leadEmails->dispatchContactMessageEmails($message);

        return $message;
    }
}
