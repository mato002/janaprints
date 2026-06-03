<?php

namespace App\Http\Controllers\Admin\Production;

use App\Enums\ProductionJobCardStatus;
use App\Enums\QualityCheckResult;
use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\QualityCheck;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QualityCheckController extends Controller
{
    public function store(Request $request, ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('create', [QualityCheck::class, $jobCard]);

        $validated = $request->validate([
            'result' => ['required', Rule::enum(QualityCheckResult::class)],
            'comments' => ['nullable', 'string', 'max:5000'],
        ]);

        $result = QualityCheckResult::from($validated['result']);

        QualityCheck::query()->create([
            'company_id' => $jobCard->company_id,
            'branch_id' => $jobCard->branch_id,
            'production_job_card_id' => $jobCard->id,
            'checked_by' => auth()->id(),
            'result' => $result,
            'comments' => $validated['comments'] ?? null,
            'checked_at' => now(),
        ]);

        match ($result) {
            QualityCheckResult::Passed => $jobCard->transitionTo(ProductionJobCardStatus::Completed),
            QualityCheckResult::Failed => $jobCard->transitionTo(ProductionJobCardStatus::OnHold),
            QualityCheckResult::ReworkRequired => $jobCard->transitionTo(ProductionJobCardStatus::Rework),
        };

        return back()->with('status', __('Quality check recorded.'));
    }
}
