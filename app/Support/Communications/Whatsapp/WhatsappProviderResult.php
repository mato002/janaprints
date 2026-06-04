<?php

namespace App\Support\Communications\Whatsapp;

use App\Enums\WhatsappDeliveryStatus;

readonly class WhatsappProviderResult
{
    public function __construct(
        public WhatsappDeliveryStatus $status,
        public ?string $providerMessageRef = null,
        public ?array $payload = null,
        public ?string $error = null,
    ) {}

    public static function queued(?array $payload = null): self
    {
        return new self(WhatsappDeliveryStatus::Queued, payload: $payload ?? ['stub' => true]);
    }

    public static function sent(string $ref, ?array $payload = null): self
    {
        return new self(WhatsappDeliveryStatus::Sent, $ref, $payload);
    }

    public static function failed(string $error, ?array $payload = null): self
    {
        return new self(WhatsappDeliveryStatus::Failed, payload: $payload, error: $error);
    }
}
