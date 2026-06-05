<?php

namespace App\Support\Inventory\Reports;

readonly class InventoryReportScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?int $warehouseId = null,
        public ?int $categoryId = null,
        public ?int $supplierId = null,
        public ?int $itemId = null,
        public string $search = '',
        public string $tab = 'stock_on_hand',
        public int $page = 1,
    ) {}

    public function cacheKey(): string
    {
        return md5(json_encode([
            $this->companyId,
            $this->branchId,
            $this->fromDate,
            $this->toDate,
            $this->warehouseId,
            $this->categoryId,
            $this->supplierId,
            $this->itemId,
            $this->search,
        ]));
    }
}
