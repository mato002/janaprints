<?php
    $pi = $workspace['printing_intelligence'] ?? [];
    $artworkFileId = $pi['artwork_file_id'] ?? 'primary';
    $supported = (bool) ($pi['supported'] ?? false);
?>

<template x-teleport="body">
<div
    x-show="piAnalysisOpen"
    x-cloak
    style="display: none;"
    class="qr-360__pi-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="qr-360-pi-modal-title"
    @keydown.escape.window="piAnalysisOpen = false"
>
    <div class="qr-360__pi-modal-backdrop" @click="piAnalysisOpen = false"></div>
    <div class="qr-360__pi-modal-panel">
        <div class="qr-360__pi-modal-head">
            <div>
                <h3 id="qr-360-pi-modal-title" class="qr-360__pi-modal-title"><?php echo e(__('Printing Intelligence Results')); ?></h3>
                <p class="qr-360__pi-modal-subtitle"><?php echo e(__('Analysis summary for this quote request artwork.')); ?></p>
            </div>
            <button type="button" class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm" @click="piAnalysisOpen = false"><?php echo e(__('Close')); ?></button>
        </div>

        <div class="qr-360__pi-modal-body">
            <template x-if="piSummary?.environment?.warnings?.length">
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900 mb-4">
                    <ul class="list-disc space-y-1 pl-4">
                        <template x-for="warning in piSummary.environment.warnings" :key="warning">
                            <li x-text="warning"></li>
                        </template>
                    </ul>
                </div>
            </template>

            <div x-show="piAnalysisLoading" class="qr-360__pi-modal-loading">
                <p class="text-sm text-slate-500"><?php echo e(__('Running analysis…')); ?></p>
            </div>

            <div x-show="! piAnalysisLoading" x-cloak class="space-y-5">
                <template x-if="piStatusMessage">
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800" x-text="piStatusMessage"></div>
                </template>

                <template x-if="piWarnings.length">
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
                        <ul class="list-disc space-y-1 pl-4">
                            <template x-for="warning in piWarnings" :key="warning">
                                <li x-text="warning"></li>
                            </template>
                        </ul>
                    </div>
                </template>

                <template x-if="piSummary">
                    <div class="qr-360__pi-tabbed">
                        <nav class="qr-360__pi-tabs" aria-label="<?php echo e(__('Printing Intelligence analysis sections')); ?>" role="tablist">
                            <button
                                type="button"
                                role="tab"
                                class="qr-360__pi-tab"
                                :class="piActiveTab === 'overview' && 'qr-360__pi-tab--active'"
                                :aria-selected="piActiveTab === 'overview'"
                                @click="setPiTab('overview')"
                            >
                                <?php echo e(__('Overview')); ?>

                            </button>
                            <button
                                type="button"
                                role="tab"
                                class="qr-360__pi-tab"
                                :class="piActiveTab === 'colour' && 'qr-360__pi-tab--active'"
                                :aria-selected="piActiveTab === 'colour'"
                                @click="setPiTab('colour')"
                            >
                                <?php echo e(__('Colour analysis')); ?>

                            </button>
                            <button
                                type="button"
                                role="tab"
                                class="qr-360__pi-tab"
                                :class="piActiveTab === 'ink' && 'qr-360__pi-tab--active'"
                                :aria-selected="piActiveTab === 'ink'"
                                @click="setPiTab('ink')"
                            >
                                <?php echo e(__('Ink estimate')); ?>

                            </button>
                            <button
                                type="button"
                                role="tab"
                                class="qr-360__pi-tab"
                                :class="piActiveTab === 'production' && 'qr-360__pi-tab--active'"
                                :aria-selected="piActiveTab === 'production'"
                                @click="setPiTab('production')"
                            >
                                <?php echo e(__('Production')); ?>

                            </button>
                            <button
                                type="button"
                                role="tab"
                                class="qr-360__pi-tab"
                                :class="piActiveTab === 'quotation' && 'qr-360__pi-tab--active'"
                                :aria-selected="piActiveTab === 'quotation'"
                                @click="setPiTab('quotation')"
                            >
                                <?php echo e(__('Quotation')); ?>

                            </button>
                        </nav>

                        <div class="qr-360__pi-tab-panels">
                        <section
                            x-show="piActiveTab === 'overview'"
                            role="tabpanel"
                            class="qr-360__pi-tab-panel qr-360__pi-section"
                        >
                            <h4 class="qr-360__pi-section-title"><?php echo e(__('File info')); ?></h4>
                            <dl class="qr-360__pi-grid">
                                <div>
                                    <dt><?php echo e(__('Filename')); ?></dt>
                                    <dd x-text="piSummary.file_info?.original_filename ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Metadata status')); ?></dt>
                                    <dd x-text="piSummary.analysis_status_label ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Colour status')); ?></dt>
                                    <dd x-text="piSummary.colour_analysis_status_label ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('File type')); ?></dt>
                                    <dd x-text="piSummary.file_info?.file_extension ? piSummary.file_info.file_extension.toUpperCase() : '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('File size')); ?></dt>
                                    <dd x-text="piSummary.file_info?.file_size_label ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('MIME type')); ?></dt>
                                    <dd x-text="piSummary.file_info?.mime_type ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Pages')); ?></dt>
                                    <dd x-text="piSummary.file_info?.page_count ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Dimensions')); ?></dt>
                                    <dd x-text="piSummary.file_info?.dimensions ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('DPI')); ?></dt>
                                    <dd x-text="piSummary.file_info?.resolution_dpi ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Colour mode')); ?></dt>
                                    <dd x-text="piSummary.file_info?.colour_mode ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Colour analysed')); ?></dt>
                                    <dd x-text="piSummary.file_info?.colour_analyzed_at ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Heavy coverage score')); ?></dt>
                                    <dd x-text="piSummary.file_info?.heavy_coverage_score != null ? Number(piSummary.file_info.heavy_coverage_score).toFixed(1) : '—'"></dd>
                                </div>
                            </dl>
                        </section>

                        <section
                            x-show="piActiveTab === 'colour'"
                            x-cloak
                            role="tabpanel"
                            class="qr-360__pi-tab-panel qr-360__pi-section"
                        >
                            <h4 class="qr-360__pi-section-title"><?php echo e(__('Colour analysis')); ?></h4>
                            <template x-if="(piSummary.detected_colours ?? piSummary.dominant_colours)?.length">
                                <div class="mb-4">
                                    <div class="qr-360__pi-section-head">
                                        <p class="qr-360__pi-subtitle mb-0"><?php echo e(__('Detected colours')); ?></p>
                                        <span
                                            class="text-xs text-slate-500"
                                            x-text="(piSummary.detected_colours_total_percent ?? 0) + '% <?php echo e(__('of artwork')); ?>'"
                                        ></span>
                                    </div>
                                    <div class="qr-360__pi-colours qr-360__pi-colours--scroll">
                                        <template x-for="(colour, index) in (piSummary.detected_colours ?? piSummary.dominant_colours)" :key="index">
                                            <div class="qr-360__pi-colour-chip">
                                                <span class="qr-360__pi-colour-swatch" :style="'background-color:' + (colour.hex || '#ccc')"></span>
                                                <span class="font-medium" x-text="colour.hex"></span>
                                                <span class="text-xs text-slate-500" x-text="Number(colour.percent ?? 0).toFixed(1) + '%'"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                            <template x-if="! (piSummary.detected_colours ?? piSummary.dominant_colours)?.length">
                                <p class="mb-4 text-sm text-slate-500"><?php echo e(__('No colour data detected yet.')); ?></p>
                            </template>

                            <template x-if="piSummary.colour_analysis_warnings?.length">
                                <div class="qr-360__pi-warnings">
                                    <p class="qr-360__pi-warnings-title"><?php echo e(__('Colour analysis warnings')); ?></p>
                                    <ul class="list-disc space-y-1 pl-4 text-sm text-amber-800">
                                        <template x-for="(warning, index) in piSummary.colour_analysis_warnings" :key="index">
                                            <li x-text="warning"></li>
                                        </template>
                                    </ul>
                                </div>
                            </template>

                            <h4 class="qr-360__pi-section-title mt-5"><?php echo e(__('Ink coverage summary')); ?></h4>
                            <dl class="qr-360__pi-grid mb-4">
                                <div>
                                    <dt><?php echo e(__('Total CMYK coverage')); ?></dt>
                                    <dd x-text="piSummary.ink_coverage?.cmyk_coverage_percent != null ? Number(piSummary.ink_coverage.cmyk_coverage_percent).toFixed(2) + '%' : '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('RGB inked area')); ?></dt>
                                    <dd x-text="piSummary.ink_coverage?.rgb_coverage_percent != null ? Number(piSummary.ink_coverage.rgb_coverage_percent).toFixed(2) + '%' : '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('White / no-ink')); ?></dt>
                                    <dd x-text="piSummary.ink_coverage?.white_area_percent != null ? Number(piSummary.ink_coverage.white_area_percent).toFixed(2) + '%' : '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Transparent')); ?></dt>
                                    <dd x-text="piSummary.ink_coverage?.transparent_area_percent != null ? Number(piSummary.ink_coverage.transparent_area_percent).toFixed(2) + '%' : '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Coverage class')); ?></dt>
                                    <dd x-text="piSummary.ink_coverage?.coverage_class_label ?? piSummary.coverage_class_label ?? '—'"></dd>
                                </div>
                                <div>
                                    <dt><?php echo e(__('Avg ink density')); ?></dt>
                                    <dd x-text="piSummary.ink_coverage?.average_ink_density_percent != null ? Number(piSummary.ink_coverage.average_ink_density_percent).toFixed(2) + '%' : '—'"></dd>
                                </div>
                            </dl>

                            <div class="qr-360__pi-coverage-report">
                                <div class="qr-360__pi-section-head">
                                    <p class="qr-360__pi-subtitle mb-0"><?php echo e(__('Ink coverage report')); ?></p>
                                    <button
                                        type="button"
                                        class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm"
                                        @click="piShowCmykBreakdown = ! piShowCmykBreakdown"
                                    >
                                        <span x-show="! piShowCmykBreakdown"><?php echo e(__('Show report')); ?></span>
                                        <span x-show="piShowCmykBreakdown" x-cloak><?php echo e(__('Hide report')); ?></span>
                                    </button>
                                </div>
                                <div x-show="piShowCmykBreakdown" x-cloak class="overflow-x-auto">
                                    <table class="qr-360__pi-coverage-table">
                                        <thead>
                                            <tr>
                                                <th scope="col"><?php echo e(__('Ink Channel')); ?></th>
                                                <th scope="col" class="text-right"><?php echo e(__('Estimated Coverage')); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><?php echo e(__('Cyan (C)')); ?></td>
                                                <td class="text-right font-medium tabular-nums" x-text="piSummary.ink_coverage?.cmyk_breakdown?.cyan != null ? '~' + Number(piSummary.ink_coverage.cmyk_breakdown.cyan).toFixed(1) + '%' : '—'"></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo e(__('Magenta (M)')); ?></td>
                                                <td class="text-right font-medium tabular-nums" x-text="piSummary.ink_coverage?.cmyk_breakdown?.magenta != null ? '~' + Number(piSummary.ink_coverage.cmyk_breakdown.magenta).toFixed(1) + '%' : '—'"></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo e(__('Yellow (Y)')); ?></td>
                                                <td class="text-right font-medium tabular-nums" x-text="piSummary.ink_coverage?.cmyk_breakdown?.yellow != null ? '~' + Number(piSummary.ink_coverage.cmyk_breakdown.yellow).toFixed(1) + '%' : '—'"></td>
                                            </tr>
                                            <tr>
                                                <td><?php echo e(__('Black (K)')); ?></td>
                                                <td class="text-right font-medium tabular-nums" x-text="piSummary.ink_coverage?.cmyk_breakdown?.black != null ? '~' + Number(piSummary.ink_coverage.cmyk_breakdown.black).toFixed(1) + '%' : '—'"></td>
                                            </tr>
                                            <tr x-show="(piSummary.ink_coverage?.cmyk_breakdown?.white ?? 0) > 0" x-cloak>
                                                <td><?php echo e(__('White / no-ink')); ?></td>
                                                <td class="text-right font-medium tabular-nums" x-text="'~' + Number(piSummary.ink_coverage.cmyk_breakdown.white).toFixed(1) + '%'"></td>
                                            </tr>
                                            <tr x-show="(piSummary.ink_coverage?.cmyk_breakdown?.transparent ?? 0) > 0" x-cloak>
                                                <td><?php echo e(__('Transparent')); ?></td>
                                                <td class="text-right font-medium tabular-nums" x-text="'~' + Number(piSummary.ink_coverage.cmyk_breakdown.transparent).toFixed(1) + '%'"></td>
                                            </tr>
                                            <tr class="border-t border-slate-200 font-semibold">
                                                <td><?php echo e(__('Total')); ?></td>
                                                <td class="text-right tabular-nums" x-text="piSummary.ink_coverage?.cmyk_breakdown?.total != null ? Number(piSummary.ink_coverage.cmyk_breakdown.total).toFixed(1) + '%' : '100.0%'"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p x-show="! piShowCmykBreakdown" class="mt-2 text-xs text-slate-500"><?php echo e(__('Area-weighted channel composition from colour analysis (totals 100%).')); ?></p>
                            </div>
                        </section>

                        <section
                            x-show="piActiveTab === 'ink'"
                            x-cloak
                            role="tabpanel"
                            class="qr-360__pi-tab-panel qr-360__pi-section"
                        >
                            <div class="qr-360__pi-section-head">
                                <h4 class="qr-360__pi-section-title"><?php echo e(__('Ink estimate')); ?></h4>
                                <template x-if="piSummary.ink_estimate">
                                    <button
                                        type="button"
                                        class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm"
                                        @click="piShowInkCmykMl = ! piShowInkCmykMl"
                                    >
                                        <span x-show="! piShowInkCmykMl"><?php echo e(__('Show CMYK ink (ml)')); ?></span>
                                        <span x-show="piShowInkCmykMl" x-cloak><?php echo e(__('Hide CMYK ink (ml)')); ?></span>
                                    </button>
                                </template>
                            </div>
                            <template x-if="piSummary.ink_estimate">
                                <div>
                                    <dl class="qr-360__pi-grid">
                                        <div>
                                            <dt><?php echo e(__('Status')); ?></dt>
                                            <dd x-text="piSummary.ink_estimate.status_label ?? '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Estimated total ink')); ?></dt>
                                            <dd x-text="piSummary.ink_estimate.estimated_total_ml != null ? Number(piSummary.ink_estimate.estimated_total_ml).toFixed(2) + ' ml' : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Estimated cost')); ?></dt>
                                            <dd x-text="piSummary.ink_estimate.estimated_ink_cost != null ? Number(piSummary.ink_estimate.estimated_ink_cost).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Confidence')); ?></dt>
                                            <dd x-text="piSummary.ink_estimate.confidence_score != null ? Number(piSummary.ink_estimate.confidence_score).toFixed(1) + '%' : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Ink profile')); ?></dt>
                                            <dd x-text="piSummary.ink_estimate.ink_profile_name ?? '—'"></dd>
                                        </div>
                                    </dl>
                                    <div x-show="piShowInkCmykMl" x-cloak class="qr-360__pi-cmyk-grid mt-4">
                                        <div class="qr-360__pi-cmyk-cell">
                                            <dt><?php echo e(__('Cyan')); ?></dt>
                                            <dd x-text="piSummary.ink_estimate.cmyk_ml?.cyan != null ? Number(piSummary.ink_estimate.cmyk_ml.cyan).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div class="qr-360__pi-cmyk-cell">
                                            <dt><?php echo e(__('Magenta')); ?></dt>
                                            <dd x-text="piSummary.ink_estimate.cmyk_ml?.magenta != null ? Number(piSummary.ink_estimate.cmyk_ml.magenta).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div class="qr-360__pi-cmyk-cell">
                                            <dt><?php echo e(__('Yellow')); ?></dt>
                                            <dd x-text="piSummary.ink_estimate.cmyk_ml?.yellow != null ? Number(piSummary.ink_estimate.cmyk_ml.yellow).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div class="qr-360__pi-cmyk-cell">
                                            <dt><?php echo e(__('Black')); ?></dt>
                                            <dd x-text="piSummary.ink_estimate.cmyk_ml?.black != null ? Number(piSummary.ink_estimate.cmyk_ml.black).toFixed(2) : '—'"></dd>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="! piSummary.ink_estimate">
                                <p class="text-sm text-slate-500"><?php echo e(__('No ink estimate yet. Run colour analysis and ink estimation.')); ?></p>
                            </template>
                        </section>

                        <section
                            x-show="piActiveTab === 'production'"
                            x-cloak
                            role="tabpanel"
                            class="qr-360__pi-tab-panel qr-360__pi-section"
                        >
                            <h4 class="qr-360__pi-section-title"><?php echo e(__('Production estimate')); ?></h4>
                            <template x-if="piSummary.production_estimate">
                                <dl class="qr-360__pi-grid">
                                    <div>
                                        <dt><?php echo e(__('Status')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.status_label ?? '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Selected machine')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.machine_code ?? piSummary.recommended_machine ?? '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Quantity')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.quantity ?? '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Run time')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.estimated_run_hours != null ? Number(piSummary.production_estimate.estimated_run_hours).toFixed(2) + ' h' : '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Total production cost')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.estimated_total_production_cost != null ? Number(piSummary.production_estimate.estimated_total_production_cost).toFixed(2) : '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Machine cost')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.estimated_machine_cost != null ? Number(piSummary.production_estimate.estimated_machine_cost).toFixed(2) : '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Labour cost')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.estimated_labour_cost != null ? Number(piSummary.production_estimate.estimated_labour_cost).toFixed(2) : '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Ink cost (included)')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.estimated_ink_cost != null ? Number(piSummary.production_estimate.estimated_ink_cost).toFixed(2) : '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Setup cost')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.estimated_setup_cost != null ? Number(piSummary.production_estimate.estimated_setup_cost).toFixed(2) : '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Overhead')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.estimated_overhead_cost != null ? Number(piSummary.production_estimate.estimated_overhead_cost).toFixed(2) : '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Confidence')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.confidence_score != null ? Number(piSummary.production_estimate.confidence_score).toFixed(1) + '%' : '—'"></dd>
                                    </div>
                                    <div>
                                        <dt><?php echo e(__('Selection score')); ?></dt>
                                        <dd x-text="piSummary.production_estimate.selection_score != null ? Number(piSummary.production_estimate.selection_score).toFixed(1) : '—'"></dd>
                                    </div>
                                </dl>
                            </template>
                            <template x-if="! piSummary.production_estimate">
                                <p class="text-sm text-slate-500"><?php echo e(__('No production estimate yet. Run machine estimation.')); ?></p>
                            </template>
                        </section>

                        <section
                            x-show="piActiveTab === 'quotation'"
                            x-cloak
                            role="tabpanel"
                            class="qr-360__pi-tab-panel qr-360__pi-section"
                        >
                            <h4 class="qr-360__pi-section-title"><?php echo e(__('Quotation recommendation')); ?></h4>
                            <template x-if="piSummary.quotation_estimate">
                                <div>
                                    <dl class="qr-360__pi-grid">
                                        <div>
                                            <dt><?php echo e(__('Status')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.status_label ?? '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Quantity')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.quantity ?? '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Total cost')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.estimated_total_cost != null ? Number(piSummary.quotation_estimate.estimated_total_cost).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Minimum selling price')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.minimum_selling_price != null ? Number(piSummary.quotation_estimate.minimum_selling_price).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Recommended price')); ?></dt>
                                            <dd class="font-semibold text-indigo-700" x-text="piSummary.quotation_estimate.recommended_selling_price != null ? Number(piSummary.quotation_estimate.recommended_selling_price).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Expected margin')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.expected_margin_percent != null ? Number(piSummary.quotation_estimate.expected_margin_percent).toFixed(1) + '%' : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Confidence')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.confidence_score != null ? Number(piSummary.quotation_estimate.confidence_score).toFixed(1) + '%' : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Material')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.estimated_material_cost != null ? Number(piSummary.quotation_estimate.estimated_material_cost).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Ink')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.estimated_ink_cost != null ? Number(piSummary.quotation_estimate.estimated_ink_cost).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Machine/process')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.estimated_machine_cost != null ? Number(piSummary.quotation_estimate.estimated_machine_cost).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Labour')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.estimated_labour_cost != null ? Number(piSummary.quotation_estimate.estimated_labour_cost).toFixed(2) : '—'"></dd>
                                        </div>
                                        <div>
                                            <dt><?php echo e(__('Electricity')); ?></dt>
                                            <dd x-text="piSummary.quotation_estimate.estimated_electricity_cost != null ? Number(piSummary.quotation_estimate.estimated_electricity_cost).toFixed(2) : '—'"></dd>
                                        </div>
                                    </dl>
                                    <template x-if="piSummary.quotation_estimate.warnings?.length">
                                        <div class="qr-360__pi-warnings mt-4">
                                            <p class="qr-360__pi-warnings-title"><?php echo e(__('Quotation estimation warnings')); ?></p>
                                            <ul class="list-disc space-y-1 pl-4 text-sm text-amber-800">
                                                <template x-for="(warning, index) in piSummary.quotation_estimate.warnings" :key="index">
                                                    <li x-text="warning"></li>
                                                </template>
                                            </ul>
                                        </div>
                                    </template>
                                </div>
                            </template>
                            <template x-if="! piSummary.quotation_estimate">
                                <p class="text-sm text-slate-500"><?php echo e(__('No quotation recommendation yet. Run quotation estimation.')); ?></p>
                            </template>
                        </section>
                        </div>
                    </div>
                </template>

                <template x-if="! piSummary">
                    <p class="text-sm text-slate-500"><?php echo e(__('No analysis results are available yet.')); ?></p>
                </template>
            </div>
        </div>

        <div class="qr-360__pi-modal-foot">
            <div class="qr-360__pi-actions">
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.analyze')): ?>
                    <?php if($supported): ?>
                        <form
                            method="POST"
                            class="inline"
                            :action="piSummary ? piRerunUrl : piRunUrl"
                            @submit.prevent="submitPiForm($event)"
                        >
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="crm-360__btn crm-360__btn--primary crm-360__btn--sm" :disabled="piAnalysisLoading">
                                <span x-show="! piSummary"><?php echo e(__('Run Analysis')); ?></span>
                                <span x-show="piSummary" x-cloak><?php echo e(__('Re-run Analysis')); ?></span>
                            </button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.analyze')): ?>
                    <form method="POST" action="<?php echo e(route('admin.public-quote-requests.printing-analysis.metadata', [$quoteRequest, $artworkFileId])); ?>" class="inline" @submit.prevent="submitPiForm($event)">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm" :disabled="piAnalysisLoading"><?php echo e(__('Metadata')); ?></button>
                    </form>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.colour-analyze')): ?>
                    <form method="POST" action="<?php echo e(route('admin.public-quote-requests.printing-analysis.colour', [$quoteRequest, $artworkFileId])); ?>" class="inline" @submit.prevent="submitPiForm($event)">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm" :disabled="piAnalysisLoading"><?php echo e(__('Colour')); ?></button>
                    </form>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.estimate-ink')): ?>
                    <form method="POST" action="<?php echo e(route('admin.public-quote-requests.printing-analysis.ink', [$quoteRequest, $artworkFileId])); ?>" class="inline" @submit.prevent="submitPiForm($event)">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm" :disabled="piAnalysisLoading"><?php echo e(__('Ink')); ?></button>
                    </form>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.artwork.estimate-production')): ?>
                    <form method="POST" action="<?php echo e(route('admin.public-quote-requests.printing-analysis.production', [$quoteRequest, $artworkFileId])); ?>" class="inline" @submit.prevent="submitPiForm($event)">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm" :disabled="piAnalysisLoading"><?php echo e(__('Machine')); ?></button>
                    </form>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.quotation.estimate')): ?>
                    <form method="POST" action="<?php echo e(route('admin.public-quote-requests.printing-analysis.quotation', [$quoteRequest, $artworkFileId])); ?>" class="inline" @submit.prevent="submitPiForm($event)">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="crm-360__btn crm-360__btn--ghost crm-360__btn--sm" :disabled="piAnalysisLoading"><?php echo e(__('Quotation')); ?></button>
                    </form>
                <?php endif; ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('printing.quotation.apply-estimate')): ?>
                    <template x-if="piSummary?.quotation_estimate?.can_apply">
                        <form method="POST" :action="piApplyUrl" class="inline" @submit.prevent="submitPiForm($event)">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="confirm_apply" value="1">
                            <button type="submit" class="crm-360__btn crm-360__btn--outline crm-360__btn--sm" :disabled="piAnalysisLoading">
                                <?php echo e(__('Apply to quotation')); ?>

                            </button>
                        </form>
                    </template>
                <?php endif; ?>
            </div>

            <template x-if="piSummary?.show_url">
                <a
                    :href="piSummary.show_url"
                    class="crm-360__btn crm-360__btn--outline crm-360__btn--sm"
                    data-turbo-frame="erp-main"
                >
                    <?php echo e(__('Open in Printing Intelligence')); ?>

                </a>
            </template>
        </div>
    </div>
</div>
</template>
<?php /**PATH C:\xampp\htdocs\jana-prints\resources\views\admin\customer-service\quote-requests\workspace\printing-intelligence-modal.blade.php ENDPATH**/ ?>