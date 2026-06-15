<?php

namespace App\DataTransferObjects\EmailIdentity;

readonly class CpanelMailboxResult
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public bool $success,
        public ?string $error = null,
        public array $metadata = [],
    ) {}
}
