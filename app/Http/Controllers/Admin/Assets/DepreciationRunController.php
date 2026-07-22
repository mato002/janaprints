<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\DepreciationRun;
use App\Services\Assets\DepreciationRunService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepreciationRunController extends Controller
{
    public function __construct(
        protected DepreciationRunService $runs,
    ) {}

    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', DepreciationRun::class);

        return redirect()->route('admin.assets.finance.dashboard', array_merge(
            $request->query(),
            ['tab' => 'runs'],
        ));
    }

    public function create(): View
    {
        $this->authorize('run', DepreciationRun::class);

        return view('admin.assets.finance.runs.create', [
            'defaultPeriod' => now()->format('Y-m'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('run', DepreciationRun::class);

        $validated = $request->validate([
            'period' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'is_dry_run' => ['nullable', 'boolean'],
        ]);

        $run = $this->runs->createDraft(
            (int) tenant()->companyId(),
            $validated['period'],
            (int) auth()->id(),
            tenant()->branchId(),
            (bool) ($validated['is_dry_run'] ?? false),
        );

        $this->runs->preview($run, tenant()->branchId());

        return redirect()
            ->route('admin.assets.finance.runs.show', $run)
            ->with('status', __('Depreciation run created.'));
    }

    public function show(DepreciationRun $run): View
    {
        $this->authorize('view', $run);

        $run->load(['entries.asset:id,asset_number,asset_name', 'executor:id,name']);

        return view('admin.assets.finance.runs.show', [
            'run' => $run,
        ]);
    }

    public function preview(DepreciationRun $run): RedirectResponse
    {
        $this->authorize('run', DepreciationRun::class);
        $this->runs->preview($run, tenant()->branchId());

        return back()->with('status', __('Depreciation preview updated.'));
    }

    public function execute(DepreciationRun $run): RedirectResponse
    {
        $this->authorize('post', $run);
        $this->runs->execute($run, (int) auth()->id(), auth()->user()->can('assets.depreciation.post'));

        return redirect()
            ->route('admin.assets.finance.runs.show', $run)
            ->with('status', __('Depreciation run completed.'));
    }

    public function cancel(DepreciationRun $run): RedirectResponse
    {
        $this->authorize('run', DepreciationRun::class);
        $this->runs->cancel($run);

        return back()->with('status', __('Depreciation run cancelled.'));
    }
}
