<?php

namespace App\Support\Communications\Email;

use App\Enums\EmailDeliveryStatus;

readonly class EmailProviderResult
{
    public function __construct(
        public EmailDeliveryStatus $status,
        public ?string $providerMessageRef = null,
        public ?array $payload = null,
        public ?string $error = null,
    ) {}

    public static function queued(?array $payload = null): self
    {
        return new self(EmailDeliveryStatus::Queued, payload: $payload ?? ['stub' => true]);
    }

    public static function sent(string $ref, ?array $payload = null): self
    {
        return new self(EmailDeliveryStatus::Sent, $ref, $payload);
    }

    public static function failed(string $error, ?array $payload = null): self
    {
        return new self(EmailDeliveryStatus::Failed, payload: $payload, error: $error);
    }
}
