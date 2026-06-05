<?php

namespace App\Support\Commercial\Reports;

readonly class CommercialPosReportScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?int $cashierId = null,
        public ?string $paymentMethod = null,
        public ?string $status = null,
        public string $search = '',
        public string $tab = 'sales_by_cashier',
        public int $page = 1,
    ) {}

    public function cacheKey(): string
    {
        return md5(json_encode([
            $this->companyId,
            $this->branchId,
            $this->fromDate,
            $this->toDate,
            $this->cashierId,
            $this->paymentMethod,
            $this->status,
            $this->search,
        ]));
    }
}
