<?php

namespace App\Support\Procurement\Performance;

readonly class SupplierPerformanceScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?int $supplierId = null,
        public ?int $warehouseId = null,
        public ?int $categoryId = null,
        public string $search = '',
        public string $tab = 'scorecard',
        public int $topLimit = 10,
        public int $page = 1,
    ) {}

    public function cacheKey(): string
    {
        return md5(json_encode([
            $this->companyId,
            $this->branchId,
            $this->fromDate,
            $this->toDate,
            $this->supplierId,
            $this->warehouseId,
            $this->categoryId,
            $this->search,
        ]));
    }
}
