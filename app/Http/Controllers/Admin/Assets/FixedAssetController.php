<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\DocumentType;
use App\Enums\FixedAssetStatus;
use App\Http\Controllers\Admin\Concerns\ScopesToTenant;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetCategory;
use App\Models\Assets\FixedAsset;
use App\Support\Platform\NumberingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FixedAssetController extends Controller
{
    use ScopesToTenant;

    public function index(): View
    {
        abort_unless(auth()->user()?->can('assets.view'), 403);

        $assets = $this->scopeToTenant(
            FixedAsset::query()->with('category')->latest()
        )->paginate(config('platform.pagination.default', 15));

        return view('admin.assets.index', compact('assets'));
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('assets.create'), 403);

        return view('admin.assets.create', [
            'categories' => AssetCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('assets.create'), 403);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        $validated = $request->validate([
            'asset_category_id' => ['required', 'exists:asset_categories,id'],
            'asset_name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'barcode' => ['nullable', 'string', 'max:100'],
            'acquisition_date' => ['required', 'date'],
            'acquisition_cost' => ['required', 'numeric', 'min:0'],
            'residual_value' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $asset = FixedAsset::query()->create([
            ...$validated,
            'company_id' => $companyId,
            'branch_id' => $branchId,
            'asset_number' => app(NumberingService::class)->next(
                DocumentType::FixedAsset,
                $companyId,
                $branchId,
            ),
            'status' => FixedAssetStatus::Active,
            'residual_value' => $validated['residual_value'] ?? 0,
        ]);

        return redirect()->route('admin.assets.show', $asset)->with('status', __('Asset registered.'));
    }

    public function show(FixedAsset $asset): View
    {
        abort_unless(auth()->user()?->can('assets.view'), 403);

        $asset->load(['category', 'maintenances', 'depreciationEntries']);

        return view('admin.assets.show', compact('asset'));
    }
}
