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
    public function transferForm(FixedAsset $asset): RedirectResponse
    {
        $this->authorize('manage', $asset);

        return redirect()
            ->route('admin.assets.custody.transfers.index')
            ->with('info', __('Use Branch Transfers for formal asset transfers with approval workflow.'));
    }

    public function transfer(Request $request, FixedAsset $asset): RedirectResponse
    {
        $this->authorize('manage', $asset);

        return redirect()
            ->route('admin.assets.custody.transfers.index')
            ->with('warning', __('Legacy transfer is disabled. Use Branch Transfers.'));
    }

    public function maintenance(Request $request, FixedAsset $asset): RedirectResponse
    {
        abort_unless(auth()->user()?->can('maintenance.view'), 403);

        return redirect()
            ->route('admin.assets.maintenance.dashboard', ['tab' => 'work-orders'])
            ->with('info', __('Use Maintenance Work Orders for preventive and corrective maintenance.'));
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
        abort_unless(auth()->user()?->can('assets.depreciation.post'), 403);

        return redirect()
            ->route('admin.assets.finance.dashboard', ['tab' => 'runs'])
            ->with('warning', __('Ad-hoc depreciation is disabled. Use Depreciation Runs to post depreciation safely with period controls.'));
    }

    public function barcode(FixedAsset $asset): View
    {
        $this->authorize('view', $asset);

        ActivityLogger::log('barcode_printed', $asset, (int) auth()->id());

        return view('admin.assets.barcode', compact('asset'));
    }
}
