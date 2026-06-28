<?php

namespace App\Support\Sales;

use App\Models\Crm\Customer;

class CustomerStatementService
{
    public function __construct(
        protected CustomerLedgerService $ledger,
    ) {}

    /**
     * @param  array{customer_id: int, from_date: string, to_date: string}  $filters
     */
    public function build(array $filters): array
    {
        $customer = Customer::query()->findOrFail($filters['customer_id']);
        $ledger = $this->ledger->build($filters);

        return [
            'customer' => $customer,
            'customer_id' => $customer->id,
            'from_date' => $filters['from_date'],
            'to_date' => $filters['to_date'],
            ...$ledger,
        ];
    }
}
