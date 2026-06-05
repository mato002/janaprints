<?php

namespace App\Support\Commercial;

use Carbon\CarbonInterface;

class PosCertificationScope
{
    public function __construct(
        public int $companyId,
        public ?int $branchId,
        public CarbonInterface $fromDate,
        public CarbonInterface $toDate,
    ) {}
}
