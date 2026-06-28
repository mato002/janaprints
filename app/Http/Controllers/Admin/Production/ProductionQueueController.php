<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\ProductionQueueStatus;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionQueue;
use App\Support\Export\TabularExportWriter;
use App\Support\Production\DepartmentQueueRegistry;
use App\Services\Production\DepartmentCommandCenterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductionQueueController extends Controller
{
    public function index(Request $request, DepartmentCommandCenterService $commandCenter): View
    {
        $this->authorize('viewWorkspace', ProductionQueue::class);

        return view('admin.production.queue.index', $commandCenter->build($request));
    }

    public function department(
        Request $request,
        string $department,
        DepartmentCommandCenterService $commandCenter,
        DepartmentQueueRegistry $registry,
    ): View|RedirectResponse {
        $this->authorize('viewWorkspace', ProductionQueue::class);

        if (! $registry->isValidSlug($department)) {
            return redirect()->route('admin.production.queue.index');
        }

        return view('admin.production.queue.index', $commandCenter->build($request, $department));
    }

    public function export(
        Request $request,
        DepartmentCommandCenterService $commandCenter,
        DepartmentQueueRegistry $registry,
        TabularExportWriter $writer,
    ): StreamedResponse {
        $this->authorize('viewWorkspace', ProductionQueue::class);

        $department = $request->query('department');
        if ($department && ! $registry->isValidSlug((string) $department)) {
            abort(404);
        }

        $format = $request->input('format', 'csv');
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            $format = 'csv';
        }

        $slug = $department ? (string) $department : 'all';
        $rows = $commandCenter->exportIndex($request, $department ? (string) $department : null)
            ->map(fn (ProductionQueue $queue) => $commandCenter->exportRow(
                $queue,
                $department ? (string) $department : null,
                $request->user(),
            ));

        $title = $department
            ? ($registry->department((string) $department)['label'] ?? ucfirst((string) $department)).' '.__('Command Centre')
            : __('Production Command Centre');

        return $writer->download(
            $format,
            $slug.'-department-register-'.now()->format('Y-m-d'),
            $commandCenter->exportHeaders($department ? (string) $department : null),
            $rows,
            $title,
        );
    }

    public function store(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('create', [ProductionQueue::class, $jobCard]);

        $validated = $request->validate([
            'work_center_id' => [
                'required',
                Rule::exists('work_centers', 'id')
                    ->where('company_id', $jobCard->company_id)
                    ->where('branch_id', $jobCard->branch_id),
            ],
            'queue_position' => ['required', 'integer', 'min:1'],
            'assigned_operator_id' => ['nullable', 'exists:users,id'],
        ]);

        $status = ! empty($validated['assigned_operator_id'])
            ? ProductionQueueStatus::Assigned
            : ProductionQueueStatus::Waiting;

        ProductionQueue::query()->create([
            ...$validated,
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'status' => $status,
        ]);

        return back()->with('status', __('Added to production queue.'));
    }

    public function update(Request $request, ProductionJobCard $jobCard, ProductionQueue $queue): RedirectResponse
    {
        $this->authorize('update', $queue);
        abort_unless($queue->production_job_card_id === $jobCard->id, 404);

        $validated = $request->validate([
            'queue_position' => ['required', 'integer', 'min:1'],
            'assigned_operator_id' => ['nullable', 'exists:users,id'],
            'status' => ['required', Rule::enum(ProductionQueueStatus::class)],
        ]);

        $queue->update($validated);

        return back()->with('status', __('Queue entry updated.'));
    }

    public function destroy(ProductionJobCard $jobCard, ProductionQueue $queue): RedirectResponse
    {
        $this->authorize('delete', $queue);
        abort_unless($queue->production_job_card_id === $jobCard->id, 404);

        $queue->delete();

        return back()->with('status', __('Removed from queue.'));
    }
}
