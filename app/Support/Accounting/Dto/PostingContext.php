<?php

namespace App\Support\Accounting\Dto;

use App\Enums\PostingEventCode;
use App\Enums\PostingModule;

class PostingContext
{
    /**
     * @param  array<string, int>  $accounts  Context account field => gl_account_id
     * @param  array<string, mixed>  $amounts  Named amounts (amount, subtotal, tax_amount, total_amount, …)
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        public int $companyId,
        public int $userId,
        public PostingEventCode $event,
        public string $sourceType,
        public int $sourceId,
        public string $journalDate,
        public ?int $branchId = null,
        public ?int $accountingPeriodId = null,
        public ?string $reference = null,
        public ?string $description = null,
        public array $accounts = [],
        public array $amounts = [],
        public array $metadata = [],
    ) {}

    public function module(): PostingModule
    {
        return $this->event->module();
    }

    public function amount(string $key, float $default = 0.0): float
    {
        return (float) ($this->amounts[$key] ?? $default);
    }

    public function accountId(string $field): ?int
    {
        $id = $this->accounts[$field] ?? null;

        return $id !== null ? (int) $id : null;
    }
}
