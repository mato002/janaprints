<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Http\Controllers\Controller;
use App\Models\Assets\AssetCapitalizationCandidate;
use App\Models\Assets\AssetCategory;
use App\Models\Branch;
use App\Models\User;
use App\Services\Assets\AssetCapitalizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetCapitalizationController extends Controller
{
    public function __construct(
        protected AssetCapitalizationService $capitalization,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AssetCapitalizationCandidate::class);

        $companyId = (int) tenant()->companyId();
        $query = AssetCapitalizationCandidate::query()
            ->where('company_id', $companyId)
            ->with(['vendor', 'category', 'goodsReceipt', 'purchaseOrder', 'goodsReceiptItem.purchaseOrderItem']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->integer('vendor_id'));
        }

        $candidates = $query->latest('received_date')->paginate(config('platform.pagination.default', 15));

        return view('admin.assets.acquisitions.queue', compact('candidates'));
    }

    public function workbench(AssetCapitalizationCandidate $candidate): View
    {
        $this->authorize('capitalize', $candidate);

        $candidate->load([
            'vendor',
            'category',
            'goodsReceipt.purchaseOrder',
            'goodsReceiptItem.purchaseOrderItem',
            'fixedAssets',
        ]);

        return view('admin.assets.acquisitions.workbench', [
            'candidate' => $candidate,
            'categories' => AssetCategory::query()->forTenant()->where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::query()->where('company_id', $candidate->company_id)->orderBy('name')->get(),
            'users' => User::query()->where('company_id', $candidate->company_id)->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function capitalize(Request $request, AssetCapitalizationCandidate $candidate): RedirectResponse
    {
        $this->authorize('capitalize', $candidate);

        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
            'asset_category_id' => ['required', Rule::exists('asset_categories', 'id')->where('company_id', $candidate->company_id)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $candidate->company_id)],
            'asset_name' => ['required', 'string', 'max:255'],
            'manufacturer' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'serial_numbers' => ['nullable', 'array'],
            'useful_life_years' => ['nullable', 'integer', 'min:1'],
            'residual_value' => ['nullable', 'numeric', 'min:0'],
            'depreciation_method' => ['nullable', 'string'],
            'assigned_custodian_user_id' => ['nullable', 'exists:users,id'],
            'warranty_start' => ['nullable', 'date'],
            'warranty_end' => ['nullable', 'date'],
            'warranty_coverage' => ['nullable', 'string', 'max:255'],
            'warranty_support_contact' => ['nullable', 'string', 'max:120'],
            'warranty_reference' => ['nullable', 'string', 'max:120'],
            'approved' => ['nullable', 'boolean'],
        ]);

        $post = auth()->user()?->can('assets.acquisition.post') ?? false;

        $assets = $this->capitalization->capitalize(
            $candidate,
            $validated,
            (int) auth()->id(),
            $post,
        );

        return redirect()
            ->route('admin.assets.acquisitions.queue')
            ->with('status', __(':count asset(s) capitalized.', ['count' => count($assets)]));
    }

    public function reject(Request $request, AssetCapitalizationCandidate $candidate): RedirectResponse
    {
        $this->authorize('reject', $candidate);

        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $this->capitalization->reject($candidate, $validated['rejection_reason'], (int) auth()->id());

        return back()->with('status', __('Capitalization candidate rejected.'));
    }
}
