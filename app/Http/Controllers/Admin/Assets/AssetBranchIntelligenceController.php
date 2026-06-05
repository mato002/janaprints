<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Services\Assets\AssetBranchIntelligenceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AssetBranchIntelligenceController extends Controller
{
    public function __construct(
        protected AssetBranchIntelligenceService $intelligence,
    ) {}

    public function __invoke(Request $request): View
    {
        abort_unless(auth()->user()?->can('assets.analytics.view'), 403);

        $companyId = (int) tenant()->companyId();
        $branchId = (int) ($request->integer('branch_id') ?: tenant()->branchId() ?: Branch::query()->where('company_id', $companyId)->value('id'));

        return view('admin.assets.intelligence.branch', [
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'selected_branch_id' => $branchId,
            'stats' => $this->intelligence->build($companyId, $branchId),
        ]);
    }
}
