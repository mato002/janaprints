<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\AssetCapitalizationPostingRecoveryService;
use App\Services\Assets\AssetCapitalizationRecoveryQueueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetCapitalizationRecoveryController extends Controller
{
    public function index(Request $request, AssetCapitalizationRecoveryQueueService $queue): View
    {
        $this->authorize('viewCapitalizationRecoveryQueue', FixedAsset::class);

        $companyId = (int) tenant()->companyId();

        return view('admin.assets.acquisitions.recovery', [
            'assets' => $queue->paginatedIndex($request, $companyId, tenant()->branchId()),
            'queue' => $queue,
            'pending_count' => $queue->pendingCount($companyId, tenant()->branchId()),
        ]);
    }

    public function post(FixedAsset $asset, AssetCapitalizationPostingRecoveryService $recovery): RedirectResponse
    {
        $this->authorize('postCapitalizationRecovery', $asset);

        $recovery->post($asset, (int) auth()->id());

        return redirect()
            ->route('admin.assets.acquisitions.recovery.index')
            ->with('status', __('Acquisition journal posted to GL.'));
    }

    public function retry(FixedAsset $asset, AssetCapitalizationPostingRecoveryService $recovery): RedirectResponse
    {
        $this->authorize('retryCapitalizationRecovery', $asset);

        $recovery->retry($asset, (int) auth()->id());

        return redirect()
            ->route('admin.assets.acquisitions.recovery.index')
            ->with('status', __('Acquisition journal posted to GL.'));
    }

    public function error(FixedAsset $asset, AssetCapitalizationPostingRecoveryService $recovery): View
    {
        $this->authorize('viewCapitalizationRecoveryError', $asset);

        return view('admin.assets.acquisitions.recovery-error', [
            'asset' => $asset->load(['category', 'capitalizationCandidate.capitalizer']),
            'reason' => $recovery->recoveryReason($asset),
        ]);
    }

    public function audit(FixedAsset $asset): View
    {
        $this->authorize('viewCapitalizationRecoveryAudit', $asset);

        $asset->load([
            'financeTimelineEntries' => fn ($q) => $q->with('user:id,name')->limit(50),
            'capitalizationCandidate.capitalizer:id,name',
        ]);

        return view('admin.assets.acquisitions.recovery-audit', compact('asset'));
    }
}
