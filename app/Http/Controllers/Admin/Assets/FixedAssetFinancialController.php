<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\DepreciationCalculationService;
use Illuminate\View\View;

class FixedAssetFinancialController extends Controller
{
    public function __construct(
        protected DepreciationCalculationService $calculator,
    ) {}

    public function show(FixedAsset $asset): View
    {
        $this->authorize('view', $asset);

        $asset->load([
            'category',
            'depreciationEntries' => fn ($q) => $q->with('journal:id,reference')->limit(12),
            'financeTimelineEntries' => fn ($q) => $q->with('user:id,name')->limit(20),
            'writeOffs',
            'disposal',
        ]);

        return view('admin.assets.finance.profile', [
            'asset' => $asset,
            'profile' => $this->calculator->financialProfile($asset),
        ]);
    }
}
