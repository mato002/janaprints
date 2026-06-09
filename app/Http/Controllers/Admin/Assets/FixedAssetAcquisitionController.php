<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\AssetManualAcquisitionPostingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;

class FixedAssetAcquisitionController extends Controller
{
    public function post(FixedAsset $asset, AssetManualAcquisitionPostingService $posting): RedirectResponse
    {
        $this->authorize('postAcquisition', $asset);

        $posting->post($asset, (int) auth()->id());

        return back()->with('status', __('Acquisition journal posted to GL.'));
    }

    public function retry(FixedAsset $asset, AssetManualAcquisitionPostingService $posting): RedirectResponse
    {
        $this->authorize('retryAcquisitionPosting', $asset);

        $posting->retry($asset, (int) auth()->id());

        return back()->with('status', __('Acquisition journal posted to GL.'));
    }

    public function journal(FixedAsset $asset): RedirectResponse
    {
        $this->authorize('viewAcquisitionJournal', $asset);

        abort_unless($asset->posted_acquisition_journal_id, 404);
        abort_unless(Route::has('admin.accounting.journals.show'), 404);

        return redirect()->route('admin.accounting.journals.show', $asset->posted_acquisition_journal_id);
    }
}
