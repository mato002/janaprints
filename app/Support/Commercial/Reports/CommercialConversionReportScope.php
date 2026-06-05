<?php

namespace App\Support\Commercial\Reports;

readonly class CommercialConversionReportScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?int $salespersonId = null,
        public ?int $leadSourceId = null,
        public ?string $customerType = null,
        public ?string $status = null,
        public string $search = '',
        public string $tab = 'full_funnel',
    ) {}

    public function cacheKey(): string
    {
        return md5(json_encode([
            $this->companyId,
            $this->branchId,
            $this->fromDate,
            $this->toDate,
            $this->salespersonId,
            $this->leadSourceId,
            $this->customerType,
            $this->status,
            $this->search,
        ]));
    }
}
