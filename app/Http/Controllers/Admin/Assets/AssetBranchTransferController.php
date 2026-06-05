<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\AssetBranchTransferStatus;
use App\Enums\AssetPhysicalCondition;
use App\Http\Controllers\Controller;
use App\Models\Assets\AssetBranchTransfer;
use App\Models\Assets\FixedAsset;
use App\Models\Branch;
use App\Services\Assets\AssetBranchTransferService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssetBranchTransferController extends Controller
{
    public function __construct(
        protected AssetBranchTransferService $transfers,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', AssetBranchTransfer::class);

        $companyId = (int) tenant()->companyId();
        $branchId = tenant()->branchId();

        $transfers = AssetBranchTransfer::query()
            ->where('company_id', $companyId)
            ->when($branchId, fn ($q) => $q->where(function ($b) use ($branchId) {
                $b->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId);
            }))
            ->with([
                'asset:id,asset_name,asset_number',
                'fromBranch:id,name',
                'toBranch:id,name',
                'requester:id,name',
            ])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('requested_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.assets.custody.transfers.index', [
            'transfers' => $transfers,
            'statuses' => AssetBranchTransferStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', AssetBranchTransfer::class);

        $companyId = (int) tenant()->companyId();

        return view('admin.assets.custody.transfers.create', [
            'assets' => FixedAsset::query()->forTenant()->notArchived()->orderBy('asset_name')->get(['id', 'asset_name', 'asset_number', 'branch_id']),
            'branches' => Branch::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']),
            'conditions' => AssetPhysicalCondition::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', AssetBranchTransfer::class);

        $validated = $request->validate([
            'fixed_asset_id' => ['required', 'exists:fixed_assets,id'],
            'from_branch_id' => ['nullable', 'exists:branches,id'],
            'to_branch_id' => ['required', 'exists:branches,id', 'different:from_branch_id'],
            'transfer_reason' => ['nullable', 'string'],
            'condition' => ['nullable', Rule::enum(AssetPhysicalCondition::class)],
            'notes' => ['nullable', 'string'],
        ]);

        $asset = FixedAsset::query()->forTenant()->findOrFail($validated['fixed_asset_id']);
        $transfer = $this->transfers->create($asset, $validated, (int) auth()->id());

        return redirect()
            ->route('admin.assets.custody.transfers.show', $transfer)
            ->with('status', __('Branch transfer requested.'));
    }

    public function show(AssetBranchTransfer $transfer): View
    {
        $this->authorize('view', $transfer);

        $transfer->load([
            'asset.category',
            'fromBranch',
            'toBranch',
            'requester:id,name',
            'approver:id,name',
            'acceptor:id,name',
        ]);

        return view('admin.assets.custody.transfers.show', [
            'transfer' => $transfer,
        ]);
    }

    public function approve(AssetBranchTransfer $transfer): RedirectResponse
    {
        $this->authorize('approve', $transfer);
        $this->transfers->approve($transfer, (int) auth()->id());

        return back()->with('status', __('Transfer approved.'));
    }

    public function accept(AssetBranchTransfer $transfer): RedirectResponse
    {
        $this->authorize('manage', $transfer);
        $this->transfers->accept($transfer, (int) auth()->id());

        return back()->with('status', __('Transfer accepted.'));
    }

    public function reject(AssetBranchTransfer $transfer): RedirectResponse
    {
        $this->authorize('manage', $transfer);
        $this->transfers->reject($transfer, (int) auth()->id());

        return back()->with('status', __('Transfer rejected.'));
    }
}
