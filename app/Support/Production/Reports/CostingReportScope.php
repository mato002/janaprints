<?php

namespace App\Support\Production\Reports;

readonly class CostingReportScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?int $customerId = null,
        public ?string $productionType = null,
        public ?int $jobCardId = null,
        public string $search = '',
        public string $tab = 'job_profitability',
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
            $this->productionType,
            $this->jobCardId,
            $this->search,
        ]));
    }
}
