<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Enums\PerformanceRating;
use App\Enums\PerformanceReviewCycle;
use App\Http\Controllers\Controller;
use App\Models\Hr\PerformanceReview;
use App\Support\Hr\PerformanceReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PerformanceReviewController extends Controller
{
    public function __construct(
        protected PerformanceReviewService $reviews,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PerformanceReview::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.performance.index', [
            'reviews' => $this->reviews->paginate($companyId, $request->only([
                'employee_id', 'cycle', 'rating', 'status',
            ])),
            'filters' => $request->only(['employee_id', 'cycle', 'rating', 'status']),
            'formData' => $this->reviews->formData($companyId),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', PerformanceReview::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        return view('admin.hr.performance.create', [
            'formData' => $this->reviews->formData($companyId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PerformanceReview::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        $validated = $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'cycle' => ['required', Rule::enum(PerformanceReviewCycle::class)],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'rating' => ['nullable', Rule::enum(PerformanceRating::class)],
            'strengths' => ['nullable', 'string', 'max:2000'],
            'improvements' => ['nullable', 'string', 'max:2000'],
            'manager_notes' => ['nullable', 'string', 'max:2000'],
            'submit' => ['nullable', 'boolean'],
        ]);

        $review = $this->reviews->create(
            $companyId,
            $validated,
            $request->user(),
            submit: (bool) ($validated['submit'] ?? false),
        );

        return redirect()
            ->route('admin.hr.performance.show', $review)
            ->with('status', __('Performance review created.'));
    }

    public function show(PerformanceReview $performanceReview): View
    {
        $this->authorize('view', $performanceReview);

        $performanceReview->load(['employee', 'reviewedBy']);

        return view('admin.hr.performance.show', [
            'review' => $performanceReview,
        ]);
    }

    public function submit(Request $request, PerformanceReview $performanceReview): RedirectResponse
    {
        $this->authorize('update', $performanceReview);

        $this->reviews->submit($performanceReview, $request->user());

        return back()->with('status', __('Performance review submitted.'));
    }

    public function destroy(PerformanceReview $performanceReview): RedirectResponse
    {
        $this->authorize('delete', $performanceReview);

        $performanceReview->delete();

        return redirect()
            ->route('admin.hr.performance.index')
            ->with('status', __('Performance review deleted.'));
    }

    public function previewKpis(Request $request): JsonResponse
    {
        $this->authorize('create', PerformanceReview::class);

        $companyId = tenant()->companyId() ?? $request->user()->company_id;

        $validated = $request->validate([
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where('company_id', $companyId),
            ],
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
        ]);

        $kpis = $this->reviews->previewKpis(
            $companyId,
            (int) $validated['employee_id'],
            $validated['period_start'],
            $validated['period_end'],
        );

        return response()->json($kpis);
    }
}
