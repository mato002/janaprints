<?php

namespace App\Support\Platform;

use App\Enums\DocumentType;

/**
 * @deprecated Use NumberGenerator directly. Kept for backward compatibility.
 */
class NumberingService
{
    public function __construct(
        protected NumberGenerator $generator,
    ) {}

    public function next(
        DocumentType $documentType,
        int $companyId,
        ?int $branchId = null,
    ): string {
        return $this->generator->generate($documentType, $companyId, $branchId);
    }
}
