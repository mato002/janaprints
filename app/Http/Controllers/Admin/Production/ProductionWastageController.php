<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\ProductionWasteType;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Support\Production\ProductionWastageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProductionWastageController extends Controller
{
    public function __construct(
        protected ProductionWastageService $wastageService,
    ) {}

    public function storeWaste(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.wastage.record'), 403);

        $validated = $this->validatePayload($request, $jobCard, requireWasteType: true);

        try {
            $this->wastageService->recordWaste($jobCard, $validated, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Production waste recorded.'));
    }

    public function storeReturn(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.wastage.record'), 403);

        $validated = $this->validatePayload($request, $jobCard, requireWasteType: false);

        try {
            $this->wastageService->recordReturn($jobCard, $validated, (int) auth()->id());
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Material return recorded.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePayload(Request $request, ProductionJobCard $jobCard, bool $requireWasteType): array
    {
        $rules = [
            'inventory_item_id' => ['required', Rule::exists('inventory_items', 'id')
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)],
            'warehouse_id' => ['required', Rule::exists('warehouses', 'id')
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)],
            'quantity' => ['required', 'numeric', 'min:0.001'],
            'employee_id' => ['nullable', Rule::exists('employees', 'id')
                ->where('company_id', $jobCard->company_id)
                ->where('branch_id', $jobCard->branch_id)],
            'machine_profile_id' => ['nullable', Rule::exists('machine_profiles', 'id')
                ->where('company_id', $jobCard->company_id)],
            'notes' => ['nullable', 'string', 'max:500'],
        ];

        if ($requireWasteType) {
            $rules['waste_type'] = ['required', Rule::enum(ProductionWasteType::class)];
            $rules['custom_reason'] = ['nullable', 'string', 'max:255'];
        }

        return $request->validate($rules);
    }
}
