<?php

namespace App\Http\Controllers\Admin\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Models\PublicQuoteRequest;
use App\Services\PrintingIntelligence\ApplyEstimateToQuotationService;
use App\Services\PrintingIntelligence\PrintArtworkAnalysisDispatcher;
use App\Services\PrintingIntelligence\PrintingIntelligenceEnvironmentService;
use App\Services\PrintingIntelligence\QrArtworkAnalysisBridgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QrArtworkAnalysisController extends Controller
{
    public function __construct(
        protected QrArtworkAnalysisBridgeService $bridge,
        protected PrintArtworkAnalysisDispatcher $dispatcher,
        protected ApplyEstimateToQuotationService $applyEstimateService,
        protected PrintingIntelligenceEnvironmentService $environment,
    ) {}

    public function run(Request $request, PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.analyze'), 403);

        return $this->dispatchSteps($publicQuoteRequest, $artworkFile, [
            'metadata', 'colour', 'ink', 'production',
        ], [
            'uploaded_by' => auth()->id(),
            'quantity' => $request->integer('quantity') ?: null,
        ], __('Printing Intelligence analysis completed.'));
    }

    public function rerun(Request $request, PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.analyze'), 403);

        return $this->dispatchSteps($publicQuoteRequest, $artworkFile, [
            'metadata', 'colour', 'ink', 'production',
        ], [
            'force_rerun' => true,
            'uploaded_by' => auth()->id(),
            'quantity' => $request->integer('quantity') ?: null,
        ], __('Printing Intelligence analysis re-run completed.'));
    }

    public function runMetadata(PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.analyze'), 403);

        return $this->dispatchSteps($publicQuoteRequest, $artworkFile, ['metadata'], [
            'force_rerun' => true,
            'uploaded_by' => auth()->id(),
        ], __('Metadata analysis completed.'));
    }

    public function runColour(PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.colour-analyze'), 403);

        return $this->dispatchSteps($publicQuoteRequest, $artworkFile, ['metadata', 'colour'], [
            'force_rerun' => true,
            'uploaded_by' => auth()->id(),
        ], __('Colour analysis completed.'));
    }

    public function runInk(PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.estimate-ink'), 403);

        return $this->dispatchSteps($publicQuoteRequest, $artworkFile, ['metadata', 'colour', 'ink'], [
            'uploaded_by' => auth()->id(),
        ], __('Ink estimate completed.'));
    }

    public function runProduction(Request $request, PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.estimate-production'), 403);

        return $this->dispatchSteps($publicQuoteRequest, $artworkFile, [
            'metadata', 'colour', 'ink', 'production',
        ], [
            'uploaded_by' => auth()->id(),
            'quantity' => $request->integer('quantity') ?: null,
        ], __('Machine estimate completed.'));
    }

    public function runQuotationEstimate(Request $request, PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.quotation.estimate'), 403);

        return $this->dispatchSteps($publicQuoteRequest, $artworkFile, [
            'metadata', 'colour', 'ink', 'production', 'quotation',
        ], [
            'uploaded_by' => auth()->id(),
            'quantity' => $request->integer('quantity') ?: null,
        ], __('Quotation estimate generated.'));
    }

    public function applyQuotationEstimate(Request $request, PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.quotation.apply-estimate'), 403);

        $quotation = $publicQuoteRequest->quotation()->first();
        abort_if($quotation === null, 422, __('This quote request is not linked to a sales quotation.'));

        $analysis = $this->bridge->findLinkedAnalysis($publicQuoteRequest, $artworkFile);
        abort_if($analysis === null, 422, __('Run quotation estimation before applying.'));

        $quotationEstimate = $analysis->quotationEstimates()->latest('id')->first();
        abort_if($quotationEstimate === null, 422, __('No quotation estimate is available to apply.'));

        if (config('printing_intelligence.require_confirmation_to_apply', true)) {
            $request->validate(['confirm_apply' => ['accepted']]);
        }

        $this->applyEstimateService->apply($quotationEstimate, $quotation, $request->user());

        $summary = $this->bridge->buildSummary($analysis->fresh([
            'inkEstimates.inkProfile',
            'productionEstimate.machineProfile',
            'quotationEstimates',
        ]));

        return $this->respondWithResult(
            [],
            $summary,
            __('Advisory estimate applied to quotation.'),
        );
    }

    public function showModal(PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.intelligence.view'), 403);

        $analysis = $this->bridge->findLinkedAnalysis($publicQuoteRequest, $artworkFile);

        return response()->json([
            'summary' => $analysis ? $this->bridge->buildSummary($analysis) : null,
            'has_analysis' => $analysis !== null,
            'environment' => $this->environment->diagnostics(),
        ]);
    }

    /**
     * @param  list<string>  $steps
     * @param  array<string, mixed>  $options
     */
    protected function dispatchSteps(
        PublicQuoteRequest $publicQuoteRequest,
        string $artworkFile,
        array $steps,
        array $options,
        string $completedMessage,
    ): JsonResponse|RedirectResponse {
        $result = $this->dispatcher->dispatchForQuoteRequest(
            $publicQuoteRequest,
            $artworkFile,
            $steps,
            $options,
        );

        return $this->respondWithResult(
            $result['warnings'] ?? [],
            $result['summary'] ?? null,
            $result['queued'] ?? false
                ? ($result['message'] ?? __('Analysis queued.'))
                : $completedMessage,
            (bool) ($result['queued'] ?? false),
        );
    }

    protected function authorizeQuoteRequest(PublicQuoteRequest $publicQuoteRequest): void
    {
        $this->authorize('view', $publicQuoteRequest);

        $companyId = (int) (tenant()->companyId() ?? auth()->user()?->company_id);
        $this->bridge->assertSameCompany($publicQuoteRequest, $companyId);
    }

    /**
     * @param  list<string>  $warnings
     * @param  array<string, mixed>|null  $summary
     */
    protected function respondWithResult(
        array $warnings,
        ?array $summary,
        string $message,
        bool $queued = false,
    ): JsonResponse|RedirectResponse {
        if (request()->expectsJson()) {
            return response()->json([
                'message' => $message,
                'warnings' => $warnings,
                'summary' => $summary,
                'queued' => $queued,
                'environment' => $this->environment->diagnostics(),
            ]);
        }

        return back()
            ->with('status', $message)
            ->with('pi_warnings', $warnings)
            ->with('pi_modal_open', true);
    }
}
