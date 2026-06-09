<?php

namespace App\Support\Communications\Bridge;

readonly class ProviderSendResult
{
    /**
     * @param  array<string, mixed>  $requestPayload
     * @param  array<string, mixed>  $responsePayload
     */
    public function __construct(
        public bool $success,
        public int $httpStatus,
        public ?string $providerMessageId = null,
        public array $requestPayload = [],
        public array $responsePayload = [],
        public ?string $error = null,
    ) {}

    /**
     * @param  array<string, mixed>  $responsePayload
     */
    public static function success(
        int $httpStatus,
        ?string $providerMessageId = null,
        array $responsePayload = [],
        array $requestPayload = [],
    ): self {
        return new self(
            success: true,
            httpStatus: $httpStatus,
            providerMessageId: $providerMessageId,
            requestPayload: $requestPayload,
            responsePayload: $responsePayload,
        );
    }

    /**
     * @param  array<string, mixed>  $responsePayload
     */
    public static function failure(
        int $httpStatus,
        string $error,
        array $responsePayload = [],
        array $requestPayload = [],
    ): self {
        return new self(
            success: false,
            httpStatus: $httpStatus,
            requestPayload: $requestPayload,
            responsePayload: $responsePayload,
            error: $error,
        );
    }
}
