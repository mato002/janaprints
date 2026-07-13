<?php

use App\Http\Controllers\Admin\PrintingIntelligence\ArtworkAnalysisController;
use App\Http\Controllers\Admin\PrintingIntelligence\PrintInkProfileController;
use App\Http\Controllers\Admin\PrintingIntelligence\PrintingIntelligenceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'tenant'])
    ->prefix('admin/printing-intelligence')
    ->name('admin.printing-intelligence.')
    ->group(function () {
        Route::middleware('permission:printing.intelligence.view')->group(function () {
            Route::get('/', [PrintingIntelligenceController::class, 'overview'])->name('overview');
            Route::get('material', [PrintingIntelligenceController::class, 'materialIntelligence'])->name('material');
            Route::get('materials', [PrintingIntelligenceController::class, 'materialIntelligence'])->name('materials');
            Route::get('machines', [PrintingIntelligenceController::class, 'machineIntelligence'])->name('machines');
            Route::get('ink', [PrintingIntelligenceController::class, 'inkIntelligence'])->name('ink');
            Route::get('inks', [PrintingIntelligenceController::class, 'inkIntelligence'])->name('inks');
            Route::get('cost', [PrintingIntelligenceController::class, 'costIntelligence'])->name('cost');
            Route::get('cost-intelligence', [PrintingIntelligenceController::class, 'costIntelligence'])->name('cost-intelligence');
            Route::get('quotations', [PrintingIntelligenceController::class, 'quotationIntelligence'])->name('quotations');
            Route::get('quotation-intelligence', [PrintingIntelligenceController::class, 'quotationIntelligence'])->name('quotation-intelligence');

            Route::get('artwork-analysis', [ArtworkAnalysisController::class, 'index'])->name('artwork-analysis.index');
            Route::get('artwork-analysis/{analysis}', [ArtworkAnalysisController::class, 'show'])->name('artwork-analysis.show');
        });

        Route::middleware('permission:printing.estimate-actual.view')->group(function () {
            Route::get('estimate-vs-actual', [PrintingIntelligenceController::class, 'estimateVsActual'])->name('estimate-vs-actual');
            Route::get('estimate-vs-actual/export', [PrintingIntelligenceController::class, 'exportEstimateVsActual'])->name('estimate-vs-actual.export');
            Route::get('estimate-vs-actual/{comparison}', [PrintingIntelligenceController::class, 'estimateVsActualShow'])
                ->whereNumber('comparison')
                ->name('estimate-vs-actual.show');
        });

        Route::post('estimate-vs-actual/compare', [PrintingIntelligenceController::class, 'runEstimateComparison'])
            ->middleware('permission:printing.estimate-actual.compare')
            ->name('estimate-vs-actual.compare');

        Route::middleware('permission:printing.calibration.view')->group(function () {
            Route::get('calibration-governance', [PrintingIntelligenceController::class, 'calibrationGovernance'])->name('calibration-governance');
            Route::get('calibration-governance/export', [PrintingIntelligenceController::class, 'exportCalibration'])->name('calibration-governance.export');
        });

        Route::middleware('permission:printing.profitability.view')->group(function () {
            Route::get('production-profitability', [PrintingIntelligenceController::class, 'productionProfitability'])->name('production-profitability');
            Route::get('production-profitability/export', [PrintingIntelligenceController::class, 'exportProfitability'])->name('production-profitability.export');
        });

        Route::post('production-profitability/generate', [PrintingIntelligenceController::class, 'generateProfitabilitySnapshots'])
            ->middleware('permission:printing.profitability.generate')
            ->name('profitability.generate');

        Route::middleware('permission:printing.executive.view')->group(function () {
            Route::get('executive-intelligence', [PrintingIntelligenceController::class, 'executiveIntelligence'])->name('executive-intelligence');
            Route::get('executive-intelligence/export', [PrintingIntelligenceController::class, 'exportForecasts'])->name('executive-intelligence.export');
        });

        Route::middleware('permission:printing.ink-profiles.view')->group(function () {
            Route::get('ink-profiles', [PrintInkProfileController::class, 'index'])->name('ink-profiles.index');
        });

        Route::post('ink-profiles', [PrintInkProfileController::class, 'store'])
            ->middleware('permission:printing.ink-profiles.manage')
            ->name('ink-profiles.store');

        Route::patch('ink-profiles/{profile}', [PrintInkProfileController::class, 'update'])
            ->middleware('permission:printing.ink-profiles.manage')
            ->name('ink-profiles.update');

        Route::delete('ink-profiles/{profile}', [PrintInkProfileController::class, 'destroy'])
            ->middleware('permission:printing.ink-profiles.manage')
            ->name('ink-profiles.destroy');

        Route::middleware('permission:printing.advisor.view')->group(function () {
            Route::get('operations-advisor', [PrintingIntelligenceController::class, 'operationsAdvisor'])->name('operations-advisor');
        });

        Route::post('operations-advisor/generate', [PrintingIntelligenceController::class, 'generateAdvisorRecommendations'])
            ->middleware('permission:printing.advisor.manage')
            ->name('advisor.generate');

        Route::post('operations-advisor/{recommendation}/acknowledge', [PrintingIntelligenceController::class, 'acknowledgeAdvisorRecommendation'])
            ->middleware('permission:printing.advisor.manage')
            ->name('advisor.acknowledge');

        Route::post('operations-advisor/{recommendation}/dismiss', [PrintingIntelligenceController::class, 'dismissAdvisorRecommendation'])
            ->middleware('permission:printing.advisor.manage')
            ->name('advisor.dismiss');

        Route::post('executive-intelligence/generate', [PrintingIntelligenceController::class, 'generateForecastSnapshots'])
            ->middleware('permission:printing.executive.forecast')
            ->name('executive.generate');

        Route::post('calibration-governance/generate', [PrintingIntelligenceController::class, 'generateCalibrationRecommendations'])
            ->middleware('permission:printing.calibration.manage')
            ->name('calibration.generate');

        Route::post('calibration-governance/{rule}/submit', [PrintingIntelligenceController::class, 'submitCalibrationRule'])
            ->middleware('permission:printing.calibration.manage')
            ->name('calibration.submit');

        Route::post('calibration-governance/{rule}/approve', [PrintingIntelligenceController::class, 'approveCalibrationRule'])
            ->middleware('permission:printing.calibration.approve')
            ->name('calibration.approve');

        Route::post('calibration-governance/{rule}/reject', [PrintingIntelligenceController::class, 'rejectCalibrationRule'])
            ->middleware('permission:printing.calibration.review')
            ->name('calibration.reject');

        Route::post('artwork-analysis/upload', [ArtworkAnalysisController::class, 'upload'])
            ->middleware('permission:printing.artwork.analyze')
            ->name('artwork-analysis.upload');

        Route::post('artwork-analysis/{analysis}/colour-analysis', [ArtworkAnalysisController::class, 'analyseColour'])
            ->middleware('permission:printing.artwork.colour-analyze')
            ->name('artwork-analysis.colour-analysis');

        Route::post('artwork-analysis/{analysis}/estimate-ink', [ArtworkAnalysisController::class, 'estimateInk'])
            ->middleware('permission:printing.artwork.estimate-ink')
            ->name('artwork-analysis.estimate-ink');

        Route::post('artwork-analysis/{analysis}/estimate-production', [ArtworkAnalysisController::class, 'estimateProduction'])
            ->middleware('permission:printing.artwork.estimate-production')
            ->name('artwork-analysis.estimate-production');

        Route::post('artwork-analysis/{analysis}/estimate-quotation', [ArtworkAnalysisController::class, 'estimateQuotation'])
            ->middleware('permission:printing.quotation.estimate')
            ->name('artwork-analysis.estimate-quotation');

        Route::post('artwork-analysis/{analysis}/quotation-estimates/{quotationEstimate}/apply', [ArtworkAnalysisController::class, 'applyQuotationEstimate'])
            ->middleware('permission:printing.quotation.apply-estimate')
            ->name('artwork-analysis.apply-quotation-estimate');

        Route::get('configuration', [PrintingIntelligenceController::class, 'configuration'])
            ->middleware('permission:printing.intelligence.configure')
            ->name('configuration');

        Route::post('configuration', [PrintingIntelligenceController::class, 'updateConfiguration'])
            ->middleware('permission:printing.intelligence.configure')
            ->name('configuration.update');
    });
