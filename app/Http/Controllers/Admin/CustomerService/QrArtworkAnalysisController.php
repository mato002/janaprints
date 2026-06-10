<?php

namespace App\Http\Controllers\Admin\CustomerService;

use App\Http\Controllers\Controller;
use App\Models\PublicQuoteRequest;
use App\Services\PrintingIntelligence\QrArtworkAnalysisBridgeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class QrArtworkAnalysisController extends Controller
{
    public function __construct(
        protected QrArtworkAnalysisBridgeService $bridge,
    ) {}

    public function run(Request $request, PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.analyze'), 403);

        $result = $this->bridge->run($publicQuoteRequest, $artworkFile, [
            'uploaded_by' => auth()->id(),
            'quantity' => $request->integer('quantity') ?: null,
        ]);

        return $this->respondWithResult(
            $result['warnings'],
            $result['summary'],
            __('Printing Intelligence analysis completed.'),
        );
    }

    public function rerun(Request $request, PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.analyze'), 403);

        $result = $this->bridge->run($publicQuoteRequest, $artworkFile, [
            'force_rerun' => true,
            'uploaded_by' => auth()->id(),
            'quantity' => $request->integer('quantity') ?: null,
        ]);

        return $this->respondWithResult(
            $result['warnings'],
            $result['summary'],
            __('Printing Intelligence analysis re-run completed.'),
        );
    }

    public function runMetadata(PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.analyze'), 403);

        $result = $this->bridge->run($publicQuoteRequest, $artworkFile, [
            'steps' => ['metadata'],
            'force_rerun' => true,
            'uploaded_by' => auth()->id(),
        ]);

        return $this->respondWithResult(
            $result['warnings'],
            $result['summary'],
            __('Metadata analysis completed.'),
        );
    }

    public function runColour(PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.colour-analyze'), 403);

        $result = $this->bridge->run($publicQuoteRequest, $artworkFile, [
            'steps' => ['metadata', 'colour'],
            'force_rerun' => true,
            'uploaded_by' => auth()->id(),
        ]);

        return $this->respondWithResult(
            $result['warnings'],
            $result['summary'],
            __('Colour analysis completed.'),
        );
    }

    public function runInk(PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.estimate-ink'), 403);

        $result = $this->bridge->run($publicQuoteRequest, $artworkFile, [
            'steps' => ['metadata', 'colour', 'ink'],
            'uploaded_by' => auth()->id(),
        ]);

        return $this->respondWithResult(
            $result['warnings'],
            $result['summary'],
            __('Ink estimate completed.'),
        );
    }

    public function runProduction(Request $request, PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.artwork.estimate-production'), 403);

        $result = $this->bridge->run($publicQuoteRequest, $artworkFile, [
            'steps' => ['metadata', 'colour', 'ink', 'production'],
            'uploaded_by' => auth()->id(),
            'quantity' => $request->integer('quantity') ?: null,
        ]);

        return $this->respondWithResult(
            $result['warnings'],
            $result['summary'],
            __('Machine estimate completed.'),
        );
    }

    public function runQuotationEstimate(Request $request, PublicQuoteRequest $publicQuoteRequest, string $artworkFile): JsonResponse|RedirectResponse
    {
        $this->authorizeQuoteRequest($publicQuoteRequest);
        abort_unless(auth()->user()?->can('printing.quotation.estimate'), 403);

        $result = $this->bridge->run($publicQuoteRequest, $artworkFile, [
            'steps' => ['metadata', 'colour', 'ink', 'production', 'quotation'],
            'uploaded_by' => auth()->id(),
            'quantity' => $request->integer('quantity') ?: null,
        ]);

        return $this->respondWithResult(
            $result['warnings'],
            $result['summary'],
            __('Quotation estimate generated.'),
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
        ]);
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
    ): JsonResponse|RedirectResponse {
        if (request()->expectsJson()) {
            return response()->json([
                'message' => $message,
                'warnings' => $warnings,
                'summary' => $summary,
            ]);
        }

        return back()
            ->with('status', $message)
            ->with('pi_warnings', $warnings)
            ->with('pi_modal_open', true);
    }
}
