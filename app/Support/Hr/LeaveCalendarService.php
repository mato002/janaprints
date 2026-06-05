<?php

namespace App\Support\Hr;

use App\Enums\LeaveRequestStatus;
use App\Models\Hr\LeaveRequest;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class LeaveCalendarService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array{date: string, requests: \Illuminate\Database\Eloquent\Collection<int, LeaveRequest>}>
     */
    public function events(int $companyId, Carbon $start, Carbon $end, array $filters = []): Collection
    {
        $query = LeaveRequest::query()
            ->where('company_id', $companyId)
            ->whereIn('status', [
                LeaveRequestStatus::Submitted->value,
                LeaveRequestStatus::SupervisorApproved->value,
                LeaveRequestStatus::Approved->value,
            ])
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($inner) use ($start, $end) {
                        $inner->where('start_date', '<=', $start)
                            ->where('end_date', '>=', $end);
                    });
            })
            ->with(['employee', 'leaveType', 'department', 'branch']);

        if (! empty($filters['branch_id'])) {
            $query->where('branch_id', $filters['branch_id']);
        }

        if (! empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        $requests = $query->orderBy('start_date')->get();

        $days = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dateString = $cursor->toDateString();
            $dayRequests = $requests->filter(function (LeaveRequest $request) use ($cursor) {
                return $cursor->between($request->start_date, $request->end_date);
            })->values();

            $days->push([
                'date' => $dateString,
                'requests' => $dayRequests,
            ]);

            $cursor->addDay();
        }

        return $days;
    }

    public function monthGrid(int $companyId, int $year, int $month, array $filters = []): Collection
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return $this->events($companyId, $start, $end, $filters);
    }

    public function weekGrid(int $companyId, ?Carbon $weekStart = null, array $filters = []): Collection
    {
        $start = ($weekStart ?? now())->copy()->startOfWeek();
        $end = $start->copy()->endOfWeek();

        return $this->events($companyId, $start, $end, $filters);
    }
}
