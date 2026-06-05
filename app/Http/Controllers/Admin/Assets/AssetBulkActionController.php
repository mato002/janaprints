<?php

namespace App\Http\Controllers\Admin\Assets;

use App\Enums\FixedAssetStatus;
use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Services\Assets\AssetRegisterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AssetBulkActionController extends Controller
{
    public function __construct(
        protected AssetRegisterService $register,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()?->can('assets.manage'), 403);

        $validated = $request->validate([
            'action' => ['required', 'in:assign,change_status,archive'],
            'asset_ids' => ['required', 'array', 'min:1'],
            'asset_ids.*' => ['integer', 'exists:fixed_assets,id'],
            'assigned_to_user_id' => ['required_if:action,assign', 'nullable', 'exists:users,id'],
            'status' => ['required_if:action,change_status', 'nullable', 'string'],
        ]);

        $userId = (int) auth()->id();
        $count = 0;

        match ($validated['action']) {
            'assign' => $count = $this->register->bulkAssignUsers(
                $validated['asset_ids'],
                (int) $validated['assigned_to_user_id'],
                $userId,
            ),
            'change_status' => $count = $this->register->bulkChangeStatus(
                $validated['asset_ids'],
                FixedAssetStatus::from($validated['status']),
                $userId,
            ),
            'archive' => $count = $this->register->bulkArchive($validated['asset_ids'], $userId),
        };

        return back()->with('status', __(':count assets updated.', ['count' => $count]));
    }
}
