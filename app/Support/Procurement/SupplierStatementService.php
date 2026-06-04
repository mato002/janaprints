<?php

namespace App\Support\Procurement;

use App\Models\Procurement\Vendor;

class SupplierStatementService
{
    public function __construct(
        protected SupplierLedgerService $ledger,
    ) {}

    /**
     * @param  array{vendor_id: int, from_date: string, to_date: string}  $filters
     */
    public function build(array $filters): array
    {
        $vendor = Vendor::query()->findOrFail($filters['vendor_id']);

        return [
            'vendor' => $vendor,
            'from_date' => $filters['from_date'],
            'to_date' => $filters['to_date'],
            ...$this->ledger->build($filters),
        ];
    }
}
