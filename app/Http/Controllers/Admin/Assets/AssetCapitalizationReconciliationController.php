<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\AssetCapitalizationReconciliation;
use App\Services\Assets\AssetCapitalizationReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetCapitalizationReconciliationController extends Controller
{
    public function __construct(
        protected AssetCapitalizationReconciliationService $reconciliation,
    ) {}

    public function index(): View
    {
        abort_unless(auth()->user()?->can('assets.reconciliation.view'), 403);

        $records = AssetCapitalizationReconciliation::query()
            ->where('company_id', tenant()->companyId())
            ->with('runner')
            ->latest('reconciliation_date')
            ->paginate(config('platform.pagination.default', 15));

        return view('admin.assets.acquisitions.reconciliation', compact('records'));
    }

    public function show(AssetCapitalizationReconciliation $reconciliation): View
    {
        abort_unless(auth()->user()?->can('assets.reconciliation.view'), 403);
        abort_unless($reconciliation->company_id === tenant()->companyId(), 403);

        return view('admin.assets.acquisitions.reconciliation-show', compact('reconciliation'));
    }

    public function store(): RedirectResponse
    {
        abort_unless(auth()->user()?->can('assets.reconciliation.view'), 403);

        $record = $this->reconciliation->run((int) tenant()->companyId(), (int) auth()->id());

        return redirect()
            ->route('admin.assets.acquisitions.reconciliation.show', $record)
            ->with('status', __('Capitalization reconciliation completed.'));
    }
}
