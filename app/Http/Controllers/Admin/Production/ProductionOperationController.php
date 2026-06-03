<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOperation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductionOperationController extends Controller
{
    public function store(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('start', $jobCard);

        $validated = $request->validate([
            'work_center_id' => [
                'required',
                Rule::exists('work_centers', 'id')
                    ->where('company_id', $jobCard->company_id)
                    ->where('branch_id', $jobCard->branch_id),
            ],
            'production_stage_id' => [
                'required',
                Rule::exists('production_stages', 'id')
                    ->where('company_id', $jobCard->company_id)
                    ->where('branch_id', $jobCard->branch_id),
            ],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
            'remarks' => ['nullable', 'string'],
        ]);

        ProductionOperation::query()->create([
            ...$validated,
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'started_at' => now(),
        ]);

        return back()->with('status', __('Production operation started.'));
    }

    public function complete(Request $request, ProductionJobCard $jobCard, ProductionOperation $operation): RedirectResponse
    {
        $this->authorize('complete', $jobCard);
        abort_unless($operation->production_job_card_id === $jobCard->id, 404);

        $operation->update([
            'ended_at' => now(),
            'remarks' => $request->input('remarks', $operation->remarks),
        ]);

        return back()->with('status', __('Operation completed.'));
    }
}
