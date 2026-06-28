<?php

namespace App\Http\Controllers\Admin\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionJobCard;
use App\Models\Production\ProductionMaterialRequirement;
use App\Support\Production\ProductionMaterialIssueService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ProductionMaterialIssueController extends Controller
{
    public function __construct(
        protected ProductionMaterialIssueService $issues,
    ) {}

    public function store(Request $request, ProductionJobCard $jobCard, ProductionMaterialRequirement $requirement): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.materials.issue'), 403);
        abort_unless($requirement->production_job_card_id === $jobCard->id, 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'numeric', 'min:0.001'],
        ]);

        try {
            $this->issues->issueFromRequirement(
                $requirement,
                (int) auth()->id(),
                isset($validated['quantity']) ? (float) $validated['quantity'] : null,
            );
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return back()->with('status', __('Material issued to production.'));
    }

    public function storeAll(ProductionJobCard $jobCard): RedirectResponse
    {
        $this->authorize('view', $jobCard);
        abort_unless(auth()->user()?->can('production.materials.issue'), 403);

        $requirements = ProductionMaterialRequirement::query()
            ->where('production_job_card_id', $jobCard->id)
            ->get();

        $issued = 0;
        foreach ($requirements as $requirement) {
            if ($requirement->remainingToIssue() <= 0) {
                continue;
            }
            try {
                $this->issues->issueFromRequirement($requirement, (int) auth()->id());
                $issued++;
            } catch (ValidationException) {
                // Skip lines with insufficient stock.
            }
        }

        return back()->with('status', __(':count material lines issued.', ['count' => $issued]));
    }
}
