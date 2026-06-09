<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetReturnCondition;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetReturn;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\AssetReturnService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetReturnController extends Controller
{
    use HandlesModalFormResponses;

    public function __construct(
        protected AssetReturnService $returns,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AssetReturn::class);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        $returns = AssetReturn::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->with([
                'asset:id,asset_name,asset_number',
                'returnedByEmployee:id,first_name,last_name',
                'receiver:id,name',
            ])
            ->when($request->filled('condition'), fn ($q) => $q->where('condition', $request->string('condition')))
            ->latest('return_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.assets.custody.returns.index', [
            'returns' => $returns,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AssetReturn::class);

        $assets = FixedAsset::query()
            ->forTenant()
            ->notArchived()
            ->orderBy('asset_name')
            ->get(['id', 'asset_name', 'asset_number']);

        return view('admin.assets.custody.returns.create', [
            'assets' => $assets,
            'conditions' => AssetReturnCondition::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('create', AssetReturn::class);

        $validated = $request->validate([
            'fixed_asset_id' => ['required', 'exists:fixed_assets,id'],
            'return_date' => ['required', 'date'],
            'condition' => ['required', Rule::enum(AssetReturnCondition::class)],
            'returned_by' => ['nullable', 'exists:employees,id'],
            'notes' => ['nullable', 'string'],
        ]);

        $asset = FixedAsset::query()->forTenant()->findOrFail($validated['fixed_asset_id']);
        $this->returns->record($asset, $validated, (int) auth()->id());

        return $this->modalOrRedirect(
            __('Asset return recorded.'),
            redirect()->route('admin.assets.custody.returns.index'),
        );
    }
}
