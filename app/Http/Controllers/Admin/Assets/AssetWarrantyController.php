<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetWarrantyStatus;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetWarranty;
use App\Models\Assets\FixedAsset;
use App\Models\Procurement\Vendor;
use App\Services\Assets\AssetCapitalizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetWarrantyController extends Controller
{
    public function __construct(
        protected AssetCapitalizationService $capitalization,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AssetWarranty::class);

        $query = AssetWarranty::query()
            ->where('company_id', tenant()->companyId())
            ->with(['asset', 'vendor']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $warranties = $query->latest('warranty_end')->paginate(config('platform.pagination.default', 15));

        return view('admin.assets.acquisitions.warranties', compact('warranties'));
    }

    public function store(Request $request, FixedAsset $asset): RedirectResponse
    {
        $this->authorize('view', $asset);
        $this->authorize('manage', AssetWarranty::class);

        $validated = $request->validate([
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'warranty_start' => ['required', 'date'],
            'warranty_end' => ['required', 'date', 'after_or_equal:warranty_start'],
            'coverage' => ['nullable', 'string', 'max:255'],
            'support_contact' => ['nullable', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->capitalization->createWarranty($asset, $validated);

        return back()->with('status', __('Warranty profile created.'));
    }

    public function update(Request $request, AssetWarranty $warranty): RedirectResponse
    {
        $this->authorize('manage', $warranty);

        $validated = $request->validate([
            'warranty_start' => ['required', 'date'],
            'warranty_end' => ['required', 'date'],
            'coverage' => ['nullable', 'string', 'max:255'],
            'support_contact' => ['nullable', 'string', 'max:120'],
            'reference_number' => ['nullable', 'string', 'max:120'],
            'status' => ['required', Rule::in(array_column(AssetWarrantyStatus::cases(), 'value'))],
            'notes' => ['nullable', 'string'],
        ]);

        $warranty->update($validated);

        return back()->with('status', __('Warranty updated.'));
    }
}
