<?php

namespace App\Support\Commercial\Reports;

readonly class CommercialCustomerReportScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?string $customerType = null,
        public ?string $status = null,
        public ?int $salespersonId = null,
        public ?string $activityStatus = null,
        public string $search = '',
        public string $tab = 'summary',
        public int $topLimit = 10,
        public int $page = 1,
        public int $dormantDays = 90,
    ) {}

    public function cacheKey(): string
    {
        return md5(json_encode([
            $this->companyId,
            $this->branchId,
            $this->fromDate,
            $this->toDate,
            $this->customerType,
            $this->status,
            $this->salespersonId,
            $this->activityStatus,
            $this->search,
        ]));
    }
}
