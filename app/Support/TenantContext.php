<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Company;

class TenantContext
{
    public function __construct(
        public ?Company $company = null,
        public ?Branch $branch = null,
        public bool $isSuperAdmin = false,
    ) {}

    public function companyId(): ?int
    {
        return $this->company?->id;
    }

    public function branchId(): ?int
    {
        return $this->branch?->id;
    }

    public function hasCompany(): bool
    {
        return $this->company !== null;
    }
}
