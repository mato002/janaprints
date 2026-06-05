<?php

namespace App\Support\Commercial\Reports;

readonly class CommercialSalesReportScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?int $customerId = null,
        public ?int $salespersonId = null,
        public ?string $status = null,
        public string $search = '',
        public string $tab = 'summary',
        public int $topLimit = 10,
        public string $topBy = 'revenue',
        public int $page = 1,
    ) {}

    public function cacheKey(): string
    {
        return md5(json_encode([
            $this->companyId,
            $this->branchId,
            $this->fromDate,
            $this->toDate,
            $this->customerId,
            $this->salespersonId,
            $this->status,
            $this->search,
        ]));
    }
}
