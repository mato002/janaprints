<?php

namespace App\Support\Reports;

readonly class HrReportScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public string $fromDate,
        public string $toDate,
        public ?int $employeeId = null,
        public ?int $departmentId = null,
        public ?int $jobTitleId = null,
        public ?string $status = null,
    ) {}
}
