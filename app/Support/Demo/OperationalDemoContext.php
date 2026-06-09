<?php

namespace App\Support\Demo;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Crm\LeadSource;
use App\Models\Crm\LeadStage;
use App\Models\Department;
use App\Models\Inventory\InventoryCategory;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\Warehouse;
use App\Models\Production\WorkCenter;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OperationalDemoContext
{
    public Carbon $periodStart;

    public Carbon $periodEnd;

    /** @var array<string, int> */
    public array $counters = [];

    /** @var Collection<int, User> */
    public Collection $users;

    /** @var Collection<int, \App\Models\Crm\Customer> */
    public Collection $customers;

    public function __construct(
        public Company $company,
        public Branch $branch,
        public Department $department,
        public User $admin,
        public ?User $salesUser = null,
        public ?Warehouse $mainWarehouse = null,
        public ?UnitOfMeasure $sheetUom = null,
        public ?UnitOfMeasure $pieceUom = null,
        public ?InventoryCategory $paperCategory = null,
        public ?WorkCenter $digitalWorkCenter = null,
        public ?LeadSource $websiteLeadSource = null,
        public ?LeadStage $newLeadStage = null,
        public ?LeadStage $wonLeadStage = null,
    ) {
        $this->periodEnd = now();
        $this->periodStart = now()->copy()->subMonths(3)->startOfDay();
        $this->users = collect();
        $this->customers = collect();
    }

    public function dateInPeriod(int $daysFromStart, ?int $hour = 10): Carbon
    {
        return $this->periodStart->copy()->addDays($daysFromStart)->setTime($hour, 0);
    }

    public function randomDateInPeriod(): Carbon
    {
        $days = random_int(0, max(1, (int) $this->periodStart->diffInDays($this->periodEnd)));

        return $this->dateInPeriod($days, random_int(8, 17));
    }

    public function nextNumber(string $prefix): string
    {
        $this->counters[$prefix] = ($this->counters[$prefix] ?? 0) + 1;

        return sprintf('%s-%04d', $prefix, $this->counters[$prefix]);
    }
}
