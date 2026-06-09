<?php

namespace App\Support\Hr;

use App\DataTransferObjects\Hr\EmployeeTimelineEvent;
use App\Enums\EmployeeDocumentCategory;
use App\Enums\LeaveRequestStatus;
use App\Enums\TrainingAssignmentStatus;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\Hr\CompensationSalaryChange;
use App\Models\Hr\EmployeeCompensation;
use App\Models\Hr\EmployeeDocument;
use App\Models\Hr\EmployeeExit;
use App\Models\Hr\EmployeeTrainingAssignment;
use App\Models\Hr\LeaveRequest;
use App\Models\Hr\PerformanceReview;
use Illuminate\Support\Collection;

class EmployeeTimelineService
{
    /**
     * @return Collection<int, EmployeeTimelineEvent>
     */
    public function eventsFor(Employee $employee, int $limit = 50): Collection
    {
        $events = collect();

        if ($employee->hire_date) {
            $events->push(EmployeeTimelineEvent::make(
                'joined',
                __('Joined organization'),
                $employee->hire_date,
                'joined',
                __('Employee :number hired', ['number' => $employee->employee_number]),
                icon: 'user-plus',
            ));
        }

        EmployeeCompensation::query()
            ->where('employee_id', $employee->id)
            ->with('changedBy')
            ->orderByDesc('effective_from')
            ->get()
            ->each(function (EmployeeCompensation $comp) use ($events) {
                $events->push(EmployeeTimelineEvent::make(
                    'compensation',
                    __('Compensation effective'),
                    $comp->effective_from,
                    'salary_change',
                    __('Basic salary :amount', ['amount' => number_format((float) $comp->basic_salary, 2)]),
                    $comp->changedBy?->name,
                    'currency-dollar',
                ));
            });

        CompensationSalaryChange::query()
            ->where('employee_id', $employee->id)
            ->with('changedBy')
            ->orderByDesc('effective_from')
            ->get()
            ->each(function (CompensationSalaryChange $change) use ($events) {
                $events->push(EmployeeTimelineEvent::make(
                    'salary_change',
                    __('Salary revised'),
                    $change->effective_from,
                    'salary_change',
                    __(':old → :new', [
                        'old' => number_format((float) $change->old_salary, 2),
                        'new' => number_format((float) $change->new_salary, 2),
                    ]),
                    $change->changedBy?->name,
                    'arrow-trending-up',
                ));
            });

        LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', LeaveRequestStatus::Approved)
            ->with('leaveType')
            ->orderByDesc('start_date')
            ->limit(20)
            ->get()
            ->each(function (LeaveRequest $request) use ($events) {
                $events->push(EmployeeTimelineEvent::make(
                    'leave',
                    __('Leave approved'),
                    $request->start_date,
                    'leave',
                    ($request->leaveType?->name ?? __('Leave')).': '.$request->days_requested.' '.__('days'),
                    icon: 'calendar',
                ));
            });

        EmployeeTrainingAssignment::query()
            ->where('employee_id', $employee->id)
            ->where('status', TrainingAssignmentStatus::Completed)
            ->with('program')
            ->orderByDesc('completed_at')
            ->limit(20)
            ->get()
            ->each(function (EmployeeTrainingAssignment $assignment) use ($events) {
                if (! $assignment->completed_at) {
                    return;
                }

                $events->push(EmployeeTimelineEvent::make(
                    'training',
                    __('Training completed'),
                    $assignment->completed_at,
                    'training',
                    $assignment->program?->name,
                    icon: 'academic-cap',
                ));
            });

        PerformanceReview::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('period_end')
            ->limit(15)
            ->get()
            ->each(function (PerformanceReview $review) use ($events) {
                $events->push(EmployeeTimelineEvent::make(
                    'performance',
                    __('Performance review'),
                    $review->period_end ?? $review->created_at,
                    'promotion',
                    __('Rating: :rating', ['rating' => $review->rating?->value ?? $review->composite_score ?? '—']),
                    icon: 'badge-check',
                ));
            });

        EmployeeDocument::query()
            ->where('employee_id', $employee->id)
            ->where('category', EmployeeDocumentCategory::WarningLetter)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->each(function (EmployeeDocument $doc) use ($events) {
                $events->push(EmployeeTimelineEvent::make(
                    'disciplinary',
                    __('Disciplinary record'),
                    $doc->created_at,
                    'disciplinary',
                    $doc->title,
                    icon: 'exclamation-triangle',
                ));
            });

        EmployeeExit::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('last_working_date')
            ->get()
            ->each(function (EmployeeExit $exit) use ($events) {
                $events->push(EmployeeTimelineEvent::make(
                    'exit',
                    __('Exit process'),
                    $exit->last_working_date ?? $exit->created_at,
                    'exit',
                    ucfirst($exit->exit_type?->value ?? '').' · '.$exit->status?->value,
                    icon: 'switch-horizontal',
                ));
            });

        ActivityLog::query()
            ->where('company_id', $employee->company_id)
            ->where('model_type', Employee::class)
            ->where('model_id', $employee->id)
            ->with('user')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->each(function (ActivityLog $log) use ($events) {
                $events->push(EmployeeTimelineEvent::make(
                    'activity',
                    ucfirst($log->action).' '.__('profile'),
                    $log->created_at ?? now(),
                    'joined',
                    null,
                    $log->user?->name,
                    'pencil',
                ));
            });

        return $events
            ->sortByDesc(fn (EmployeeTimelineEvent $event) => $event->eventDatetime->timestamp)
            ->take($limit)
            ->values();
    }
}
