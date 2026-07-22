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

    public function index(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', AssetReturn::class);

        return redirect()->route('admin.assets.custody.dashboard', array_merge(
            $request->query(),
            ['tab' => 'returns'],
        ));
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
            redirect()->route('admin.assets.custody.dashboard', ['tab' => 'returns']),
        );
    }
}
