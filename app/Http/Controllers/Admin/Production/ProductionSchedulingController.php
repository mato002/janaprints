<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\ProductionPriority;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\WorkCenter;
use App\Services\Production\ProductionSchedulingWorkspaceService;
use App\Support\Export\TabularExportWriter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductionSchedulingController extends Controller
{
    public function index(Request $request, ProductionSchedulingWorkspaceService $workspace): View
    {
        $this->authorize('viewSchedulingWorkspace', ProductionJobCard::class);

        $viewMode = $request->query('view') === 'calendar' ? 'calendar' : 'list';
        $month = $request->query('month', now()->format('Y-m'));

        return view('admin.production.scheduling.index', [
            'kpis' => $workspace->kpiCounts(),
            'jobs' => $viewMode === 'list' ? $workspace->paginatedIndex($request) : null,
            'calendar' => $viewMode === 'calendar' ? $workspace->calendarMonth($request) : null,
            'workCenters' => WorkCenter::query()->forTenant()->orderBy('name')->get(['id', 'name']),
            'statuses' => ProductionJobCardStatus::cases(),
            'priorities' => ProductionPriority::cases(),
            'filters' => [
                'status' => $request->query('status'),
                'priority' => $request->query('priority'),
                'work_center_id' => $request->query('work_center_id'),
                'date' => $request->query('date'),
                'date_from' => $request->query('date_from'),
                'date_to' => $request->query('date_to'),
                'search' => $request->query('search'),
                'view' => $viewMode,
                'month' => $month,
            ],
            'viewMode' => $viewMode,
            'workspace' => $workspace,
        ]);
    }

    public function export(Request $request, ProductionSchedulingWorkspaceService $workspace): StreamedResponse
    {
        $this->authorize('viewSchedulingWorkspace', ProductionJobCard::class);

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $jobs = $workspace->exportIndex($request);

        $headers = [
            __('Job Number'),
            __('Customer'),
            __('Work Center'),
            __('Status'),
            __('Priority'),
            __('Planned Start'),
            __('Planned End'),
            __('Due Date'),
        ];

        $rows = $jobs->map(function (ProductionJobCard $job) use ($workspace) {
            $centers = implode(', ', $workspace->workCenterNames($job));
            $dueDate = $job->planned_end_date ?? $job->salesOrder?->required_date;

            return [
                $job->job_card_number,
                $job->customer?->company_name ?? '',
                $centers !== '' ? $centers : '',
                str_replace('_', ' ', ucfirst($job->status->value)),
                str_replace('_', ' ', ucfirst($job->priority->value)),
                $job->planned_start_date?->format('Y-m-d') ?? '',
                $job->planned_end_date?->format('Y-m-d') ?? '',
                $dueDate?->format('Y-m-d') ?? '',
            ];
        });

        return app(TabularExportWriter::class)->download(
            $format,
            'production-scheduling-'.now()->format('Y-m-d'),
            $headers,
            $rows,
            __('Production Scheduling'),
        );
    }
}
