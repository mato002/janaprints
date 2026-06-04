<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\Warehouse;
use App\Models\User;
use App\Support\Platform\FormSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WarehouseManagerController extends Controller
{
    public function __construct(
        protected FormSettingsService $formSettings,
    ) {}

    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('manage', $warehouse);

        $warehouse->load('managers');

        $users = User::query()
            ->where('company_id', $warehouse->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.inventory.warehouses.managers', [
            'warehouse' => $warehouse,
            'users' => $users,
            'formFields' => $this->formSettings->resolvedFields(
                'warehouse.manager_assignment',
                $warehouse->company_id,
                $warehouse->branch_id,
            ),
        ]);
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('manage', $warehouse);

        $validated = $request->validate($this->formSettings->mergeValidationRules('warehouse.manager_assignment', [
            'manager_ids' => ['array'],
            'manager_ids.*' => [
                Rule::exists('users', 'id')->where('company_id', $warehouse->company_id),
            ],
        ], $warehouse->company_id, $warehouse->branch_id));

        $sync = collect($validated['manager_ids'] ?? [])
            ->mapWithKeys(fn ($userId) => [(int) $userId => ['is_manager' => true]])
            ->all();

        $warehouse->managers()->sync($sync);

        return redirect()->route('admin.inventory.warehouses.show', $warehouse)->with('status', __('Store managers updated.'));
    }
}
