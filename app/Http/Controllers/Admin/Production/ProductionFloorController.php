<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Assets\FixedAsset;
use App\Models\Production\ProductionJobCard;
use App\Services\Assets\MachineJobAssignmentService;
use App\Services\Production\ProductionFloorActionService;
use App\Services\Production\ProductionFloorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionFloorController extends Controller
{
    public function index(Request $request, ProductionFloorService $floor): View
    {
        $this->authorize('viewAny', ProductionJobCard::class);

        return view('admin.production.floor.index', $floor->build($request));
    }

    public function panel(ProductionJobCard $jobCard, ProductionFloorService $floor): JsonResponse
    {
        $this->authorize('view', $jobCard);

        return response()->json($floor->panel($jobCard));
    }

    public function assignMachine(
        Request $request,
        ProductionJobCard $jobCard,
        MachineJobAssignmentService $assignments,
    ): RedirectResponse {
        $this->authorize('update', $jobCard);
        abort_unless($request->user()?->can('machines.assign'), 403);

        $validated = $request->validate([
            'assigned_machine_asset_id' => ['nullable', 'exists:fixed_assets,id'],
        ]);

        if (! empty($validated['assigned_machine_asset_id'])) {
            $machine = FixedAsset::query()
                ->forTenant()
                ->whereHas('machineProfile')
                ->findOrFail($validated['assigned_machine_asset_id']);

            $assignments->assignToJob($jobCard, $machine, (int) $request->user()->id);
        } else {
            $jobCard->update(['assigned_machine_asset_id' => null]);
        }

        return redirect()
            ->route('admin.production.floor.index', $request->only(['search', 'stage', 'machine_id', 'vendor_id', 'priority', 'overdue']))
            ->with('status', __('Machine assignment updated.'));
    }

    public function quickPassQc(ProductionJobCard $jobCard, ProductionFloorActionService $actions): RedirectResponse
    {
        $actions->quickPassQc($jobCard, request()->user());

        return back()->with('status', __('Quality check passed.'));
    }
}
