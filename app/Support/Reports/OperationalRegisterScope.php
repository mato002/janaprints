<?php

namespace App\Support\Reports;

readonly class OperationalRegisterScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?string $department = null,
        public ?int $customerId = null,
        public ?int $machineId = null,
        public ?int $operatorId = null,
        public ?int $vendorId = null,
        public ?string $paymentStatus = null,
        public ?string $productionStatus = null,
        public string $search = '',
        public string $register = 'daily_sales',
        public string $preset = '',
    ) {}

    public function cacheKey(): string
    {
        return md5(json_encode([
            $this->companyId,
            $this->branchId,
            $this->fromDate,
            $this->toDate,
            $this->department,
            $this->customerId,
            $this->machineId,
            $this->operatorId,
            $this->vendorId,
            $this->paymentStatus,
            $this->productionStatus,
            $this->search,
            $this->register,
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toFilterArray(): array
    {
        return array_filter([
            'from_date' => $this->fromDate,
            'to_date' => $this->toDate,
            'department' => $this->department,
            'customer_id' => $this->customerId,
            'machine_id' => $this->machineId,
            'operator_id' => $this->operatorId,
            'vendor_id' => $this->vendorId,
            'payment_status' => $this->paymentStatus,
            'production_status' => $this->productionStatus,
            'search' => $this->search !== '' ? $this->search : null,
            'register' => $this->register,
            'preset' => $this->preset !== '' ? $this->preset : null,
            'branch_id' => $this->branchId,
        ], fn ($value) => $value !== null && $value !== '');
    }

    public function intelligenceScope(): IntelligenceScope
    {
        return new IntelligenceScope(
            companyId: $this->companyId,
            branchId: $this->branchId,
            fromDate: $this->fromDate,
            toDate: $this->toDate,
            customerId: $this->customerId,
            vendorId: $this->vendorId,
            status: $this->productionStatus,
        );
    }
}
