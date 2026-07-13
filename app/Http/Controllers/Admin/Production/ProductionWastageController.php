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

    public function store(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.wastage.record'), 403);

        $flowType = $request->input('flow_type', 'waste');
        $isReturn = $flowType === 'return';

        $validated = $this->validatePayload($request, $jobCard, requireWasteType: ! $isReturn);

        try {
            if ($isReturn) {
                $this->wastageService->recordReturn($jobCard, $validated, (int) auth()->id());
            } else {
                $this->wastageService->recordWaste($jobCard, $validated, (int) auth()->id());
            }
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', $isReturn
            ? __('Material return recorded.')
            : __('Production waste recorded.'));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatePayload(Request $request, ProductionJobCard $jobCard, bool $requireWasteType): array
    {
        $rules = [
            'flow_type' => ['nullable', Rule::in(['waste', 'return'])],
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
