<?php

namespace App\Http\Controllers\Admin\Operations;

use App\Http\Controllers\Controller;
use App\Operations\BackgroundJobsCenter;
use App\Services\Operations\BackgroundJobMonitorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BackgroundJobsController extends Controller
{
    public function __construct(
        protected BackgroundJobMonitorService $monitor,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BackgroundJobsCenter::class);

        $filters = [
            'search' => $request->string('search')->toString() ?: null,
            'type' => $request->string('type')->toString() ?: 'all',
            'status' => $request->string('status')->toString() ?: 'all',
            'queue' => $request->string('queue')->toString() ?: 'all',
        ];

        return view('admin.operations.jobs.index', [
            'jobs' => $this->monitor->paginate($filters),
            'metrics' => $this->monitor->summaryMetrics(),
            'filters' => $filters,
            'typeOptions' => $this->monitor->typeOptions(),
            'statusOptions' => $this->monitor->statusOptions(),
            'queueOptions' => $this->monitor->queueOptions(),
            'canRetry' => $request->user()->can('retry', BackgroundJobsCenter::class),
            'canCancel' => $request->user()->can('cancel', BackgroundJobsCenter::class),
        ]);
    }

    public function show(Request $request, string $reference): JsonResponse
    {
        $this->authorize('view', BackgroundJobsCenter::class);

        $job = $this->monitor->find($reference);

        return response()->json([
            'reference' => $job['reference'],
            'job_id' => $job['job_id'],
            'queue' => $job['queue'],
            'type' => $job['type']->value,
            'type_label' => $job['type']->shortLabel(),
            'status' => $job['status']->value,
            'status_label' => $job['status']->label(),
            'started_label' => $job['started_label'],
            'completed_label' => $job['completed_label'],
            'duration_label' => $job['duration_label'],
            'attempts' => $job['attempts'],
            'job_class' => $job['job_class'],
            'error' => $job['error'],
            'error_full' => $job['error_full'],
        ]);
    }

    public function retry(Request $request, string $reference): RedirectResponse
    {
        $this->authorize('retry', BackgroundJobsCenter::class);

        $this->monitor->retry($reference);

        return redirect()
            ->route('admin.operations.jobs.index')
            ->with('success', __('Job queued for retry.'));
    }

    public function cancel(Request $request, string $reference): RedirectResponse
    {
        $this->authorize('cancel', BackgroundJobsCenter::class);

        $this->monitor->cancel($reference, $request->user());

        return redirect()
            ->route('admin.operations.jobs.index')
            ->with('success', __('Job cancelled.'));
    }

    public function retryFailed(Request $request): RedirectResponse
    {
        $this->authorize('retry', BackgroundJobsCenter::class);

        $count = $this->monitor->retryAllFailed();

        return redirect()
            ->route('admin.operations.jobs.index')
            ->with('success', $count > 0
                ? __('Retried :count failed jobs.', ['count' => $count])
                : __('No failed jobs to retry.'));
    }
}
