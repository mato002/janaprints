<?php

namespace App\Support\Reports;

readonly class IntelligenceScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?int $warehouseId = null,
        public ?int $categoryId = null,
        public ?int $vendorId = null,
        public ?string $status = null,
        public ?int $customerId = null,
        public ?string $kpiCategory = null,
    ) {}
}
