<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\AssetRegisterReconciliation;
use App\Services\Assets\AssetReconciliationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AssetReconciliationController extends Controller
{
    public function __construct(
        protected AssetReconciliationService $reconciliation,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', AssetRegisterReconciliation::class);

        $reconciliations = AssetRegisterReconciliation::query()
            ->where('company_id', tenant()->companyId())
            ->with('reconciler:id,name')
            ->latest('reconciliation_date')
            ->paginate(20);

        return view('admin.assets.finance.reconciliation.index', [
            'reconciliations' => $reconciliations,
        ]);
    }

    public function store(): RedirectResponse
    {
        $this->authorize('run', AssetRegisterReconciliation::class);

        $record = $this->reconciliation->run(
            (int) tenant()->companyId(),
            (int) auth()->id(),
        );

        return redirect()
            ->route('admin.assets.finance.reconciliation.show', $record)
            ->with('status', __('Reconciliation completed.'));
    }

    public function show(AssetRegisterReconciliation $reconciliation): View
    {
        $this->authorize('view', $reconciliation);

        return view('admin.assets.finance.reconciliation.show', [
            'reconciliation' => $reconciliation->load('reconciler:id,name'),
        ]);
    }
}
