<?php

namespace App\Support\Accounting\Posting;

class PostingRuleValidationResult
{
    public const STATUS_VALID = 'valid';

    public const STATUS_WARNING = 'warning';

    public const STATUS_BROKEN = 'broken';

    /**
     * @param  list<array{level: string, code: string, message: string}>  $issues
     */
    public function __construct(
        public readonly string $status,
        public readonly array $issues = [],
    ) {}

    public function isValid(): bool
    {
        return $this->status === self::STATUS_VALID;
    }

    public function isBroken(): bool
    {
        return $this->status === self::STATUS_BROKEN;
    }

    public function label(): string
    {
        return match ($this->status) {
            self::STATUS_VALID => __('Valid'),
            self::STATUS_WARNING => __('Warning'),
            self::STATUS_BROKEN => __('Broken'),
            default => $this->status,
        };
    }

    public function badgeVariant(): string
    {
        return match ($this->status) {
            self::STATUS_VALID => 'success',
            self::STATUS_WARNING => 'warning',
            self::STATUS_BROKEN => 'danger',
            default => 'neutral',
        };
    }
}
