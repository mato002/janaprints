<?php

namespace App\Http\Controllers\Admin\PrintingIntelligence;

use App\Enums\ColourAnalysisStatus;
use App\Enums\InkEstimationStatus;
use App\Enums\ProductionEstimationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PrintingIntelligence\StoreArtworkAnalysisRequest;
use App\Models\Inventory\InventoryItem;
use App\Models\PrintingIntelligence\PrintArtworkAnalysis;
use App\Models\PrintingIntelligence\PrintQuotationEstimate;
use App\Services\PrintingIntelligence\ApplyEstimateToQuotationService;
use App\Services\PrintingIntelligence\ArtworkAnalysisService;
use App\Services\PrintingIntelligence\PrintArtworkAnalysisDispatcher;
use App\Services\PrintingIntelligence\PrintingIntelligenceEnvironmentService;
use App\Services\PrintingIntelligence\QuotationEstimationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ArtworkAnalysisController extends Controller
{
    public function __construct(
        protected ArtworkAnalysisService $analysisService,
        protected PrintArtworkAnalysisDispatcher $dispatcher,
        protected QuotationEstimationService $quotationEstimationService,
        protected ApplyEstimateToQuotationService $applyEstimateService,
        protected PrintingIntelligenceEnvironmentService $environment,
    ) {}

    public function index(): View
    {
        $this->authorizeView();

        $analyses = PrintArtworkAnalysis::query()
            ->forTenant()
            ->with(['uploadedBy', 'quotation', 'productionJobCard'])
            ->latest('id')
            ->limit(50)
            ->get();

        return view('admin.printing-intelligence.artwork-analysis.index', [
            'analyses' => $analyses,
            'config' => config('printing_intelligence'),
            'environment' => $this->environment->diagnostics(),
        ]);
    }

    public function upload(StoreArtworkAnalysisRequest $request): RedirectResponse
    {
        $analysis = $this->analysisService->analyzeUploadedFile(
            $request->file('file'),
            $request->context(),
        );

        return redirect()
            ->route('admin.printing-intelligence.artwork-analysis.show', $analysis)
            ->with('status', __('Artwork uploaded and analyzed.'));
    }

    public function analyseColour(PrintArtworkAnalysis $analysis): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.artwork.colour-analyze'), 403);
        $this->assertTenantAnalysis($analysis);
        $this->assertArtworkFileExists($analysis);
        $this->assertColourNotProcessing($analysis);

        $result = $this->dispatcher->dispatchForAnalysis($analysis, ['colour']);

        return redirect()
            ->route('admin.printing-intelligence.artwork-analysis.show', $analysis)
            ->with('status', $result['message']);
    }

    public function estimateInk(PrintArtworkAnalysis $analysis): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.artwork.estimate-ink'), 403);
        $this->assertTenantAnalysis($analysis);
        $this->assertInkNotProcessing($analysis);
        abort_unless(
            in_array($analysis->colour_analysis_status, [
                ColourAnalysisStatus::Completed,
                ColourAnalysisStatus::ManualReview,
            ], true),
            422,
            __('Colour analysis must be completed before ink estimation.'),
        );

        $result = $this->dispatcher->dispatchForAnalysis($analysis, ['ink']);

        return redirect()
            ->route('admin.printing-intelligence.artwork-analysis.show', $analysis)
            ->with('status', $result['message']);
    }

    public function estimateProduction(PrintArtworkAnalysis $analysis): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.artwork.estimate-production'), 403);
        $this->assertTenantAnalysis($analysis);
        $this->assertProductionNotProcessing($analysis);

        $result = $this->dispatcher->dispatchForAnalysis($analysis, ['production']);

        return redirect()
            ->route('admin.printing-intelligence.artwork-analysis.show', $analysis)
            ->with('status', $result['message']);
    }

    public function estimateQuotation(Request $request, PrintArtworkAnalysis $analysis): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.quotation.estimate'), 403);
        $this->assertTenantAnalysis($analysis);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'material_inventory_item_id' => ['nullable', 'integer', 'exists:inventory_items,id'],
            'material_unit_cost_override' => ['nullable', 'numeric', 'min:0'],
            'material_quantity_override' => ['nullable', 'numeric', 'min:0'],
            'minimum_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:99.9'],
            'target_margin_percent' => ['nullable', 'numeric', 'min:0', 'max:99.9'],
            'wastage_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $options = [
            'quantity' => (int) ($validated['quantity'] ?? 1),
            'material_inventory_item_id' => $validated['material_inventory_item_id'] ?? null,
            'material_unit_cost_override' => isset($validated['material_unit_cost_override']) ? (float) $validated['material_unit_cost_override'] : null,
            'material_quantity_override' => isset($validated['material_quantity_override']) ? (float) $validated['material_quantity_override'] : null,
            'minimum_margin_percent' => isset($validated['minimum_margin_percent']) ? (float) $validated['minimum_margin_percent'] : null,
            'target_margin_percent' => isset($validated['target_margin_percent']) ? (float) $validated['target_margin_percent'] : null,
            'wastage_percent' => isset($validated['wastage_percent']) ? (float) $validated['wastage_percent'] : null,
            'quotation_id' => $analysis->quotation_id,
        ];

        if ($this->environment->shouldQueueAnalysis()) {
            $result = $this->dispatcher->dispatchForAnalysis($analysis, ['quotation'], $options);
        } else {
            $this->quotationEstimationService->estimate($analysis, $options);
            $result = ['message' => __('Quotation estimate generated.')];
        }

        return redirect()
            ->route('admin.printing-intelligence.artwork-analysis.show', $analysis)
            ->with('status', $result['message']);
    }

    public function applyQuotationEstimate(Request $request, PrintArtworkAnalysis $analysis, PrintQuotationEstimate $quotationEstimate): RedirectResponse
    {
        abort_unless(auth()->user()?->can('printing.quotation.apply-estimate'), 403);
        $this->assertTenantAnalysis($analysis);
        abort_unless((int) $quotationEstimate->print_artwork_analysis_id === (int) $analysis->id, 404);
        abort_unless($analysis->quotation_id, 422, __('Artwork is not linked to a quotation.'));

        if (config('printing_intelligence.require_confirmation_to_apply', true)) {
            $request->validate(['confirm_apply' => ['accepted']]);
        }

        $quotation = $analysis->quotation;
        abort_if($quotation === null, 404);

        $this->applyEstimateService->apply($quotationEstimate, $quotation, $request->user());

        return redirect()
            ->route('admin.printing-intelligence.artwork-analysis.show', $analysis)
            ->with('status', __('Advisory estimate applied to quotation.'));
    }

    public function show(PrintArtworkAnalysis $analysis): View
    {
        $this->authorizeView();
        $this->assertTenantAnalysis($analysis);

        $analysis->load(['pages', 'quotation', 'productionJobCard', 'uploadedBy', 'branch', 'inkEstimates.inkProfile', 'productionEstimate.machineProfile', 'quotationEstimates']);

        $materialItems = InventoryItem::query()
            ->where('company_id', $analysis->company_id)
            ->where('is_active', true)
            ->orderBy('item_name')
            ->limit(200)
            ->get(['id', 'item_name', 'sku']);

        return view('admin.printing-intelligence.artwork-analysis.show', [
            'analysis' => $analysis,
            'inkEstimate' => $analysis->inkEstimates->first(),
            'productionEstimate' => $analysis->productionEstimate,
            'quotationEstimate' => $analysis->quotationEstimates->first(),
            'materialItems' => $materialItems,
            'piConfig' => config('printing_intelligence'),
            'environment' => $this->environment->diagnostics(),
        ]);
    }

    protected function authorizeView(): void
    {
        abort_unless(auth()->user()?->can('printing.intelligence.view'), 403);
    }

    protected function assertTenantAnalysis(PrintArtworkAnalysis $analysis): void
    {
        abort_unless(
            (int) $analysis->company_id === (int) (tenant()->companyId() ?? auth()->user()?->company_id),
            404,
        );
    }

    protected function assertArtworkFileExists(PrintArtworkAnalysis $analysis): void
    {
        abort_unless(Storage::disk($analysis->disk)->exists($analysis->file_path), 404);
    }

    protected function assertColourNotProcessing(PrintArtworkAnalysis $analysis): void
    {
        abort_if(
            $analysis->colour_analysis_status === ColourAnalysisStatus::Processing,
            409,
            __('Colour analysis is already in progress.'),
        );
    }

    protected function assertInkNotProcessing(PrintArtworkAnalysis $analysis): void
    {
        abort_if(
            $analysis->inkEstimates()->where('estimation_status', InkEstimationStatus::Processing)->exists(),
            409,
            __('Ink estimation is already in progress.'),
        );
    }

    protected function assertProductionNotProcessing(PrintArtworkAnalysis $analysis): void
    {
        abort_if(
            $analysis->productionEstimate?->estimation_status === ProductionEstimationStatus::Processing,
            409,
            __('Production estimation is already in progress.'),
        );
    }
}
