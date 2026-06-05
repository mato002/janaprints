<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\DepreciationPostingStatus;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetDepreciationEntry;
use App\Models\Assets\DepreciationRun;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepreciationEntryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', DepreciationRun::class);

        $entries = AssetDepreciationEntry::query()
            ->whereHas('asset', function ($q) {
                $q->where('company_id', tenant()->companyId());
                if (tenant()->branchId()) {
                    $q->where('branch_id', tenant()->branchId());
                }
            })
            ->with(['asset:id,asset_number,asset_name', 'run:id,run_number', 'journal:id,reference'])
            ->when($request->filled('posting_status'), fn ($q) => $q->where('posting_status', $request->string('posting_status')))
            ->latest('period_date')
            ->paginate(25)
            ->withQueryString();

        return view('admin.assets.finance.entries.index', [
            'entries' => $entries,
            'statuses' => DepreciationPostingStatus::cases(),
        ]);
    }
}
