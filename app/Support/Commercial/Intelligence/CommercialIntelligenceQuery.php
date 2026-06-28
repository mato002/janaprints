<?php

namespace App\Support\Commercial\Intelligence;

use App\Enums\ProductionJobCardStatus;
use App\Models\Production\JobCostSheet;
use App\Support\Reports\IntelligenceScope;
use Illuminate\Database\Eloquent\Builder;

class CommercialIntelligenceQuery
{
    /**
     * @return Builder<JobCostSheet>
     */
    public function costSheets(IntelligenceScope $scope, bool $completedJobsOnly = true): Builder
    {
        $query = JobCostSheet::query()
            ->where('job_cost_sheets.company_id', $scope->companyId);

        if ($scope->branchId) {
            $query->where('job_cost_sheets.branch_id', $scope->branchId);
        }

        if ($scope->fromDate) {
            $query->whereDate('job_cost_sheets.calculated_at', '>=', $scope->fromDate);
        }

        if ($scope->toDate) {
            $query->whereDate('job_cost_sheets.calculated_at', '<=', $scope->toDate);
        }

        if ($completedJobsOnly) {
            $query->whereHas('jobCard', fn (Builder $job) => $job->whereIn('status', [
                ProductionJobCardStatus::Completed,
                ProductionJobCardStatus::ReadyForDispatch,
                ProductionJobCardStatus::Returned,
            ]));
        }

        return $query;
    }
}
