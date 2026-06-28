<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\QualityCheckResult;
use App\Enums\QualityFailReason;
use App\Enums\QualityReworkReason;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use App\Support\Production\QualityInspectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QualityCheckController extends Controller
{
    public function __construct(
        protected QualityInspectionService $inspections,
    ) {}

    public function store(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('create', [QualityCheck::class, $jobCard]);

        $validated = $request->validate([
            'result' => ['required', Rule::enum(QualityCheckResult::class)],
            'comments' => ['nullable', 'string', 'max:5000'],
            'inspection_date' => ['nullable', 'date'],
            'fail_reason' => ['nullable', Rule::enum(QualityFailReason::class)],
            'rework_reason' => ['nullable', Rule::enum(QualityReworkReason::class)],
            'estimated_rework_qty' => ['nullable', 'numeric', 'min:0'],
            'actual_rework_qty' => ['nullable', 'numeric', 'min:0'],
            'requires_customer_approval' => ['nullable', 'boolean'],
            'checklist' => ['nullable', 'array'],
            'checklist.*.line_id' => ['nullable'],
            'checklist.*.label' => ['nullable', 'string', 'max:120'],
            'checklist.*.passed' => ['nullable', 'boolean'],
        ]);

        if (in_array($validated['result'], [QualityCheckResult::Failed->value, QualityCheckResult::ReworkRequired->value], true)
            && empty($validated['rework_reason']) && empty($validated['fail_reason'])) {
            return back()->withInput()->withErrors([
                'rework_reason' => __('A fail or rework reason is required when inspection fails.'),
            ]);
        }

        $this->inspections->recordInspection($jobCard, $validated, (int) auth()->id());

        return back()->with('status', __('Quality inspection recorded.'));
    }

    public function approveCustomer(
        Request $request,
        ProductionJobCard $jobCard,
        QualityCheck $qualityCheck,
    ): RedirectResponse {
        $this->authorize('approveCustomerHold', $jobCard);

        abort_unless($qualityCheck->production_job_card_id === $jobCard->id, 404);

        $this->inspections->approveCustomerHold($jobCard, $qualityCheck, (int) auth()->id());

        return back()->with('status', __('Customer approval recorded. Job is ready for dispatch.'));
    }
}
