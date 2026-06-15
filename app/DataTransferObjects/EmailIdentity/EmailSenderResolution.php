<?php

namespace App\DataTransferObjects\EmailIdentity;

class EmailSenderResolution
{
    public function __construct(
        public string $purpose,
        public string $mailboxPurpose,
        public ?string $address,
        public bool $configured,
        public bool $usedFallback,
    ) {}
}
