<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionOperation;
use App\Services\Production\JobProductionControlService;
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
            'assigned_employee_id' => $this->scopedEmployeeRule($jobCard),
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

    public function update(Request $request, ProductionJobCard $jobCard, ProductionOperation $operation): RedirectResponse
    {
        $this->authorize('start', $jobCard);
        abort_unless($operation->production_job_card_id === $jobCard->id, 404);
        abort_unless(app(JobProductionControlService::class)->operatorAssignmentAvailable(), 404);

        $validated = $request->validate([
            'assigned_employee_id' => $this->scopedEmployeeRule($jobCard),
            'remarks' => ['nullable', 'string', 'max:5000'],
        ]);

        $operation->update([
            'assigned_employee_id' => $validated['assigned_employee_id'] ?? null,
            'remarks' => $validated['remarks'] ?? $operation->remarks,
        ]);

        return back()->with('status', __('Operation updated.'));
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

    /**
     * @return array<int, mixed>
     */
    protected function scopedEmployeeRule(ProductionJobCard $jobCard): array
    {
        return [
            'nullable',
            'integer',
            function (string $attribute, mixed $value, \Closure $fail) use ($jobCard): void {
                if ($value === null || $value === '') {
                    return;
                }

                $valid = Employee::query()
                    ->where('id', $value)
                    ->where('company_id', $jobCard->company_id)
                    ->where('branch_id', $jobCard->branch_id)
                    ->where('is_active', true)
                    ->exists();

                if (! $valid) {
                    $fail(__('The selected operator is not valid for this job.'));
                }
            },
        ];
    }
}
