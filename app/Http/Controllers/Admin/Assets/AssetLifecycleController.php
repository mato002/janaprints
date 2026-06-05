<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetDisposalStatus;
use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Support\ActivityLogger;
use App\Services\Assets\AssetDisposalAccountingService;
use App\Support\Assets\AssetDepreciationService;
use App\Support\Assets\AssetLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetLifecycleController extends Controller
{
    public function transferForm(FixedAsset $asset): View
    {
        $this->authorize('manage', $asset);

        return view('admin.assets.transfer', [
            'asset' => $asset,
            'branches' => Branch::query()->where('company_id', $asset->company_id)->orderBy('name')->get(),
        ]);
    }

    public function transfer(Request $request, FixedAsset $asset): RedirectResponse
    {
        $this->authorize('manage', $asset);

        $validated = $request->validate([
            'to_branch_id' => ['nullable', 'exists:branches,id'],
            'to_user_id' => ['nullable', 'exists:users,id'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        AssetLifecycleService::transfer($asset, $validated, (int) auth()->id());
        ActivityLogger::log('transferred', $asset->fresh(), (int) auth()->id(), $validated);

        return redirect()->route('admin.assets.show', $asset)->with('status', __('Asset transferred.'));
    }

    public function maintenance(Request $request, FixedAsset $asset): RedirectResponse
    {
        abort_unless(auth()->user()?->can('assets.manage'), 403);

        $validated = $request->validate([
            'maintenance_type' => ['required', 'string', 'max:30'],
            'scheduled_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        AssetLifecycleService::scheduleMaintenance($asset, $validated);

        return back()->with('status', __('Maintenance scheduled.'));
    }

    public function repair(FixedAsset $asset): RedirectResponse
    {
        abort_unless(auth()->user()?->can('assets.manage'), 403);
        AssetLifecycleService::startRepair($asset);

        return back()->with('status', __('Asset marked under repair.'));
    }

    public function repairComplete(FixedAsset $asset): RedirectResponse
    {
        abort_unless(auth()->user()?->can('assets.manage'), 403);
        AssetLifecycleService::completeRepair($asset);

        return back()->with('status', __('Repair completed.'));
    }

    public function disposeForm(FixedAsset $asset): View
    {
        $this->authorize('dispose', $asset);

        return view('admin.assets.dispose', compact('asset'));
    }

    public function dispose(Request $request, FixedAsset $asset): RedirectResponse
    {
        $this->authorize('dispose', $asset);

        $validated = $request->validate([
            'disposal_date' => ['required', 'date'],
            'disposal_proceeds' => ['nullable', 'numeric', 'min:0'],
            'disposal_method' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
        ]);

        $disposal = app(AssetDisposalAccountingService::class)->dispose(
            $asset,
            $validated,
            (int) auth()->id(),
        );

        if (auth()->user()?->can('assets.disposal.post') && $disposal->status === AssetDisposalStatus::Approved) {
            app(AssetDisposalAccountingService::class)->post($disposal, (int) auth()->id());
        }

        return redirect()->route('admin.assets.index')->with('status', __('Asset disposed.'));
    }

    public function depreciate(Request $request, FixedAsset $asset): RedirectResponse
    {
        abort_unless(auth()->user()?->can('assets.manage'), 403);

        $validated = $request->validate([
            'period_date' => ['required', 'date'],
        ]);

        AssetDepreciationService::runPeriod($asset, $validated['period_date'], (int) auth()->id());

        return back()->with('status', __('Depreciation posted.'));
    }

    public function barcode(FixedAsset $asset): View
    {
        $this->authorize('view', $asset);

        ActivityLogger::log('barcode_printed', $asset, (int) auth()->id());

        return view('admin.assets.barcode', compact('asset'));
    }
}
