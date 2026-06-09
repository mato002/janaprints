<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetWriteOffReason;
use App\Http\Controllers\Admin\Concerns\HandlesModalFormResponses;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetWriteOff;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\AssetWriteOffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetWriteOffController extends Controller
{
    use HandlesModalFormResponses;

    public function __construct(
        protected AssetWriteOffService $writeOffs,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', AssetWriteOff::class);

        $writeOffs = AssetWriteOff::query()
            ->where('company_id', tenant()->companyId())
            ->with(['asset:id,asset_number,asset_name', 'creator:id,name'])
            ->latest('write_off_date')
            ->paginate(20);

        return view('admin.assets.finance.write-offs.index', [
            'writeOffs' => $writeOffs,
        ]);
    }

    public function create(): View
    {
        $this->authorize('manage', AssetWriteOff::class);

        $assets = FixedAsset::query()
            ->forTenant()
            ->notArchived()
            ->orderBy('asset_name')
            ->get(['id', 'asset_number', 'asset_name']);

        return view('admin.assets.finance.write-offs.create', [
            'assets' => $assets,
            'reasons' => AssetWriteOffReason::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse|Response
    {
        $this->authorize('manage', AssetWriteOff::class);

        $validated = $request->validate([
            'fixed_asset_id' => ['required', 'exists:fixed_assets,id'],
            'reason' => ['required', Rule::enum(AssetWriteOffReason::class)],
            'write_off_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $asset = FixedAsset::query()->forTenant()->findOrFail($validated['fixed_asset_id']);
        $writeOff = $this->writeOffs->create($asset, $validated, (int) auth()->id());

        return $this->modalOrRedirect(
            __('Write-off request created: :no', ['no' => $writeOff->writeoff_no]),
            redirect()->route('admin.assets.finance.write-offs.index'),
        );
    }

    public function approve(AssetWriteOff $writeOff): RedirectResponse
    {
        $this->authorize('manage', AssetWriteOff::class);
        $this->writeOffs->approve($writeOff, (int) auth()->id());

        return back()->with('status', __('Write-off approved.'));
    }

    public function post(AssetWriteOff $writeOff): RedirectResponse
    {
        $this->authorize('post', $writeOff);
        $this->writeOffs->post($writeOff, (int) auth()->id());

        return back()->with('status', __('Write-off posted.'));
    }
}
