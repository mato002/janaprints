<?php

namespace App\Support\Hr;

use App\Support\Reports\HrReportScope;

readonly class HrKpiScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public string $dimension = 'company',
        public ?int $employeeId = null,
        public ?int $departmentId = null,
        public ?int $jobTitleId = null,
        public ?int $supervisorJobTitleId = null,
        public ?string $status = null,
    ) {}

    public function toReportScope(): HrReportScope
    {
        return new HrReportScope(
            companyId: $this->companyId,
            branchId: $this->branchId,
            fromDate: $this->fromDate,
            toDate: $this->toDate,
            employeeId: $this->employeeId,
            departmentId: $this->departmentId,
            jobTitleId: $this->jobTitleId,
            status: $this->status,
        );
    }
}
