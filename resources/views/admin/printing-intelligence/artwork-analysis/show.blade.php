<x-admin-layout :title="__('Artwork Analysis Details')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Artwork Analysis'), 'url' => route('admin.printing-intelligence.artwork-analysis.index')],
    ['label' => $analysis->original_filename],
]">
    <x-admin.page-header
        :title="$analysis->original_filename"
        :description="__('Artwork metadata, colour, ink, production, and quotation recommendations (PI5).')"
    />

    @include('admin.printing-intelligence.partials.nav')

    <div class="mb-4 flex flex-wrap items-center gap-3">
        <a href="{{ route('admin.printing-intelligence.artwork-analysis.index') }}"
           class="text-xs font-medium text-slate-600 hover:text-slate-900">&larr; {{ __('Back to list') }}</a>

        @can('printing.artwork.colour-analyze')
            @if ($analysis->colour_analysis_status !== \App\Enums\ColourAnalysisStatus::Processing)
                <form method="POST" action="{{ route('admin.printing-intelligence.artwork-analysis.colour-analysis', $analysis) }}" class="inline">
                    @csrf
                    <button type="submit" class="erp-btn-secondary text-xs">{{ __('Run Colour Analysis') }}</button>
                </form>
            @endif
        @endcan

        @can('printing.artwork.estimate-ink')
            @if (in_array($analysis->colour_analysis_status, [\App\Enums\ColourAnalysisStatus::Completed, \App\Enums\ColourAnalysisStatus::ManualReview], true)
                && ! $analysis->inkEstimates->contains(fn ($e) => $e->estimation_status === \App\Enums\InkEstimationStatus::Processing))
                <form method="POST" action="{{ route('admin.printing-intelligence.artwork-analysis.estimate-ink', $analysis) }}" class="inline">
                    @csrf
                    <button type="submit" class="erp-btn-secondary text-xs">{{ __('Run Ink Estimation') }}</button>
                </form>
            @endif
        @endcan

        @can('printing.artwork.estimate-production')
            @if (! ($productionEstimate?->estimation_status === \App\Enums\ProductionEstimationStatus::Processing))
                <form method="POST" action="{{ route('admin.printing-intelligence.artwork-analysis.estimate-production', $analysis) }}" class="inline">
                    @csrf
                    <button type="submit" class="erp-btn-secondary text-xs">{{ __('Run Production Estimation') }}</button>
                </form>
            @endif
        @endcan
    </div>

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-4">{{ session('status') }}</x-admin.alert>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('File info') }}</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-500">{{ __('Metadata status') }}</dt>
                    <dd><span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $analysis->analysis_status->badgeClass()])>{{ $analysis->analysis_status->label() }}</span></dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Colour status') }}</dt>
                    <dd>
                        @if ($analysis->colour_analysis_status)
                            <span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $analysis->colour_analysis_status->badgeClass()])>{{ $analysis->colour_analysis_status->label() }}</span>
                        @else — @endif
                    </dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Pages') }}</dt><dd>{{ $analysis->page_count ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('DPI') }}</dt><dd>{{ $analysis->resolution_dpi ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Colour analysed') }}</dt><dd>{{ $analysis->colour_analyzed_at?->format('Y-m-d H:i:s') ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Heavy coverage score') }}</dt><dd>{{ $analysis->heavy_coverage_score !== null ? number_format((float) $analysis->heavy_coverage_score, 1) : '—' }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Ink coverage summary') }}</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-500">{{ __('Total CMYK coverage') }}</dt><dd>{{ $analysis->cmyk_coverage_percent !== null ? number_format((float) $analysis->cmyk_coverage_percent, 2).'%' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('RGB inked area') }}</dt><dd>{{ $analysis->rgb_coverage_percent !== null ? number_format((float) $analysis->rgb_coverage_percent, 2).'%' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('White / no-ink') }}</dt><dd>{{ $analysis->white_area_percent !== null ? number_format((float) $analysis->white_area_percent, 2).'%' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Transparent') }}</dt><dd>{{ $analysis->transparent_area_percent !== null ? number_format((float) $analysis->transparent_area_percent, 2).'%' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Coverage class') }}</dt>
                    <dd>
                        @if ($analysis->coverage_class)
                            <span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $analysis->coverage_class->badgeClass()])>{{ $analysis->coverage_class->label() }}</span>
                        @else — @endif
                    </dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Avg ink density') }}</dt><dd>{{ $analysis->average_ink_density_percent !== null ? number_format((float) $analysis->average_ink_density_percent, 2).'%' : '—' }}</dd></div>
            </dl>

            <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('CMYK breakdown') }}</h4>
            <dl class="grid grid-cols-4 gap-2 text-sm">
                @foreach (['cyan' => __('Cyan'), 'magenta' => __('Magenta'), 'yellow' => __('Yellow'), 'black' => __('Black')] as $key => $label)
                    <div class="rounded-md bg-slate-50 p-2 text-center">
                        <dt class="text-[10px] uppercase text-slate-500">{{ $label }}</dt>
                        <dd class="font-semibold">{{ $analysis->{$key.'_coverage_percent'} !== null ? number_format((float) $analysis->{$key.'_coverage_percent'}, 1).'%' : '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        </x-admin.card>
    </div>

    @if (! empty($analysis->dominant_colours))
        <x-admin.card class="mt-6">
            <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Dominant colours') }}</h3>
            <div class="flex flex-wrap gap-3">
                @foreach ($analysis->dominant_colours as $colour)
                    <div class="flex items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm">
                        <span class="inline-block h-6 w-6 rounded border border-slate-300" style="background-color: {{ $colour['hex'] ?? '#ccc' }}"></span>
                        <span>{{ $colour['hex'] ?? '—' }}</span>
                        <span class="text-xs text-slate-500">{{ ($colour['percent'] ?? 0).'%' }}</span>
                    </div>
                @endforeach
            </div>
        </x-admin.card>
    @endif

    @if (! empty($analysis->colour_analysis_warnings))
        <x-admin.card class="mt-6">
            <h3 class="mb-3 text-sm font-semibold text-amber-900">{{ __('Colour analysis warnings') }}</h3>
            <ul class="list-inside list-disc space-y-1 text-sm text-amber-900">
                @foreach ($analysis->colour_analysis_warnings as $warning)
                    <li>{{ is_string($warning) ? $warning : json_encode($warning) }}</li>
                @endforeach
            </ul>
        </x-admin.card>
    @endif

    <x-admin.card class="mt-6">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Ink estimate') }}</h3>

        @if ($inkEstimate)
            <dl class="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                <div><dt class="text-xs text-slate-500">{{ __('Status') }}</dt>
                    <dd><span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $inkEstimate->estimation_status->badgeClass()])>{{ $inkEstimate->estimation_status->label() }}</span></dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Estimated total ink') }}</dt><dd>{{ $inkEstimate->estimated_total_ml !== null ? number_format((float) $inkEstimate->estimated_total_ml, 2).' ml' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Estimated cost') }}</dt><dd>{{ $inkEstimate->estimated_ink_cost !== null ? number_format((float) $inkEstimate->estimated_ink_cost, 2) : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Confidence') }}</dt><dd>{{ $inkEstimate->confidence_score !== null ? number_format((float) $inkEstimate->confidence_score, 1).'%' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Formula') }}</dt><dd>{{ $inkEstimate->formula_version ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Coverage area') }}</dt><dd>{{ $inkEstimate->coverage_area_sq_m !== null ? number_format((float) $inkEstimate->coverage_area_sq_m, 4).' m²' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Cartridge use') }}</dt><dd>{{ $inkEstimate->estimated_cartridge_percent !== null ? number_format((float) $inkEstimate->estimated_cartridge_percent, 1).'%' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Ink profile') }}</dt><dd>{{ $inkEstimate->inkProfile?->name ?? '—' }}</dd></div>
            </dl>

            <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('CMYK ink (ml)') }}</h4>
            <dl class="grid grid-cols-4 gap-2 text-sm">
                @foreach (['cyan' => __('Cyan'), 'magenta' => __('Magenta'), 'yellow' => __('Yellow'), 'black' => __('Black')] as $key => $label)
                    <div class="rounded-md bg-slate-50 p-2 text-center">
                        <dt class="text-[10px] uppercase text-slate-500">{{ $label }}</dt>
                        <dd class="font-semibold">{{ $inkEstimate->{'estimated_'.$key.'_ml'} !== null ? number_format((float) $inkEstimate->{'estimated_'.$key.'_ml'}, 2) : '—' }}</dd>
                    </div>
                @endforeach
            </dl>

            @if (! empty($inkEstimate->warnings))
                <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-amber-800">{{ __('Ink estimation warnings') }}</h4>
                <ul class="list-inside list-disc space-y-1 text-sm text-amber-900">
                    @foreach ($inkEstimate->warnings as $warning)
                        <li>{{ is_string($warning) ? $warning : json_encode($warning) }}</li>
                    @endforeach
                </ul>
            @endif

            @if (! empty($inkEstimate->metadata))
                <details class="mt-4">
                    <summary class="cursor-pointer text-xs font-semibold text-slate-700">{{ __('Technical estimation data') }}</summary>
                    <pre class="mt-2 overflow-x-auto rounded-md bg-slate-50 p-3 text-xs text-slate-800">{{ json_encode($inkEstimate->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            @endif
        @else
            <p class="text-sm text-slate-600">{{ __('No ink estimate yet. Complete colour analysis, then run ink estimation.') }}</p>
        @endif
    </x-admin.card>

    <x-admin.card class="mt-6">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Production estimate') }}</h3>

        @if ($productionEstimate)
            <dl class="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                <div><dt class="text-xs text-slate-500">{{ __('Status') }}</dt>
                    <dd><span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $productionEstimate->estimation_status->badgeClass()])>{{ $productionEstimate->estimation_status->label() }}</span></dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Selected machine') }}</dt><dd>{{ $productionEstimate->machineProfile?->machine_code ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Run time') }}</dt><dd>{{ $productionEstimate->estimated_run_hours !== null ? number_format((float) $productionEstimate->estimated_run_hours, 2).' h' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Total production cost') }}</dt><dd>{{ $productionEstimate->estimated_total_production_cost !== null ? number_format((float) $productionEstimate->estimated_total_production_cost, 2) : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Machine cost') }}</dt><dd>{{ $productionEstimate->estimated_machine_cost !== null ? number_format((float) $productionEstimate->estimated_machine_cost, 2) : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Labour cost') }}</dt><dd>{{ $productionEstimate->estimated_labour_cost !== null ? number_format((float) $productionEstimate->estimated_labour_cost, 2) : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Ink cost (included)') }}</dt><dd>{{ $productionEstimate->estimated_ink_cost !== null ? number_format((float) $productionEstimate->estimated_ink_cost, 2) : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Confidence') }}</dt><dd>{{ $productionEstimate->confidence_score !== null ? number_format((float) $productionEstimate->confidence_score, 1).'%' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Selection score') }}</dt><dd>{{ $productionEstimate->selection_score !== null ? number_format((float) $productionEstimate->selection_score, 1) : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Formula') }}</dt><dd>{{ $productionEstimate->formula_version ?? '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Setup cost') }}</dt><dd>{{ $productionEstimate->estimated_setup_cost !== null ? number_format((float) $productionEstimate->estimated_setup_cost, 2) : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Overhead') }}</dt><dd>{{ $productionEstimate->estimated_overhead_cost !== null ? number_format((float) $productionEstimate->estimated_overhead_cost, 2) : '—' }}</dd></div>
            </dl>

            @if (! empty($productionEstimate->machine_alternatives))
                <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Alternative machines') }}</h4>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                                <th class="py-2 pr-3">{{ __('Machine') }}</th>
                                <th class="py-2 pr-3">{{ __('Run (h)') }}</th>
                                <th class="py-2 pr-3">{{ __('Cost') }}</th>
                                <th class="py-2 pr-3">{{ __('Score') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach ($productionEstimate->machine_alternatives as $alt)
                                <tr>
                                    <td class="py-2 pr-3">{{ $alt['machine_code'] ?? '—' }}</td>
                                    <td class="py-2 pr-3">{{ isset($alt['estimated_run_hours']) ? number_format((float) $alt['estimated_run_hours'], 2) : '—' }}</td>
                                    <td class="py-2 pr-3">{{ isset($alt['estimated_total_production_cost']) ? number_format((float) $alt['estimated_total_production_cost'], 2) : '—' }}</td>
                                    <td class="py-2 pr-3">{{ isset($alt['selection_score']) ? number_format((float) $alt['selection_score'], 1) : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if (! empty($productionEstimate->warnings))
                <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-amber-800">{{ __('Production estimation warnings') }}</h4>
                <ul class="list-inside list-disc space-y-1 text-sm text-amber-900">
                    @foreach ($productionEstimate->warnings as $warning)
                        <li>{{ is_string($warning) ? $warning : json_encode($warning) }}</li>
                    @endforeach
                </ul>
            @endif

            @if (! empty($productionEstimate->metadata))
                <details class="mt-4">
                    <summary class="cursor-pointer text-xs font-semibold text-slate-700">{{ __('Technical production data') }}</summary>
                    <pre class="mt-2 overflow-x-auto rounded-md bg-slate-50 p-3 text-xs text-slate-800">{{ json_encode($productionEstimate->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            @endif
        @else
            <p class="text-sm text-slate-600">{{ __('No production estimate yet. Run production estimation to select a machine and calculate costs.') }}</p>
        @endif
    </x-admin.card>

    <x-admin.card class="mt-6">
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Quotation recommendation') }}</h3>

        @can('printing.quotation.estimate')
            <form method="POST" action="{{ route('admin.printing-intelligence.artwork-analysis.estimate-quotation', $analysis) }}" class="mb-4 grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                @csrf
                <div>
                    <label class="text-xs text-slate-500">{{ __('Quantity') }}</label>
                    <input type="number" name="quantity" min="1" value="{{ old('quantity', $quotationEstimate?->quantity ?? 1) }}" class="erp-input w-full text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500">{{ __('Material item') }}</label>
                    <select name="material_inventory_item_id" class="erp-select w-full text-sm">
                        <option value="">{{ __('Select material') }}</option>
                        @foreach ($materialItems as $item)
                            <option value="{{ $item->id }}" @selected(old('material_inventory_item_id', $quotationEstimate?->material_inventory_item_id) == $item->id)>{{ $item->item_name }} ({{ $item->sku }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">{{ __('Manual material unit cost') }}</label>
                    <input type="number" step="0.0001" name="material_unit_cost_override" value="{{ old('material_unit_cost_override') }}" class="erp-input w-full text-sm" placeholder="{{ __('Optional override') }}">
                </div>
                <div>
                    <label class="text-xs text-slate-500">{{ __('Minimum margin %') }}</label>
                    <input type="number" step="0.1" name="minimum_margin_percent" value="{{ old('minimum_margin_percent', $quotationEstimate?->minimum_margin_percent ?? $piConfig['default_minimum_margin_percent']) }}" class="erp-input w-full text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500">{{ __('Target margin %') }}</label>
                    <input type="number" step="0.1" name="target_margin_percent" value="{{ old('target_margin_percent', $quotationEstimate?->target_margin_percent ?? $piConfig['default_target_margin_percent']) }}" class="erp-input w-full text-sm">
                </div>
                <div>
                    <label class="text-xs text-slate-500">{{ __('Wastage %') }}</label>
                    <input type="number" step="0.1" name="wastage_percent" value="{{ old('wastage_percent', $piConfig['default_wastage_percent']) }}" class="erp-input w-full text-sm">
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <button type="submit" class="erp-btn-secondary text-xs">{{ __('Generate Quotation Estimate') }}</button>
                </div>
            </form>
        @endcan

        @if ($quotationEstimate)
            <dl class="grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
                <div><dt class="text-xs text-slate-500">{{ __('Status') }}</dt>
                    <dd><span @class(['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', $quotationEstimate->estimation_status->badgeClass()])>{{ $quotationEstimate->estimation_status->label() }}</span></dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Total cost') }}</dt><dd>{{ number_format((float) $quotationEstimate->estimated_total_cost, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Minimum selling price') }}</dt><dd>{{ number_format((float) $quotationEstimate->minimum_selling_price, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Recommended price') }}</dt><dd class="font-semibold">{{ number_format((float) $quotationEstimate->recommended_selling_price, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Expected margin') }}</dt><dd>{{ $quotationEstimate->expected_margin_percent !== null ? number_format((float) $quotationEstimate->expected_margin_percent, 1).'%' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Confidence') }}</dt><dd>{{ $quotationEstimate->confidence_score !== null ? number_format((float) $quotationEstimate->confidence_score, 1).'%' : '—' }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Material') }}</dt><dd>{{ number_format((float) $quotationEstimate->estimated_material_cost, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Ink') }}</dt><dd>{{ number_format((float) $quotationEstimate->estimated_ink_cost, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Machine/process') }}</dt><dd>{{ number_format((float) $quotationEstimate->estimated_machine_cost, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Labour') }}</dt><dd>{{ number_format((float) $quotationEstimate->estimated_labour_cost, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Electricity') }}</dt><dd>{{ number_format((float) $quotationEstimate->estimated_electricity_cost, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Overhead') }}</dt><dd>{{ number_format((float) $quotationEstimate->estimated_overhead_cost, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Wastage') }}</dt><dd>{{ number_format((float) $quotationEstimate->estimated_wastage_cost, 2) }}</dd></div>
                <div><dt class="text-xs text-slate-500">{{ __('Formula') }}</dt><dd>{{ $quotationEstimate->formula_version ?? '—' }}</dd></div>
            </dl>

            @if (! empty($quotationEstimate->warnings))
                <h4 class="mb-2 mt-4 text-xs font-semibold uppercase tracking-wide text-amber-800">{{ __('Quotation estimation warnings') }}</h4>
                <ul class="list-inside list-disc space-y-1 text-sm text-amber-900">
                    @foreach ($quotationEstimate->warnings as $warning)
                        <li>{{ is_string($warning) ? $warning : json_encode($warning) }}</li>
                    @endforeach
                </ul>
            @endif

            @if ($analysis->quotation && $piConfig['allow_apply_to_quotation'] && in_array($quotationEstimate->estimation_status, [\App\Enums\QuotationEstimationStatus::Completed, \App\Enums\QuotationEstimationStatus::ManualReview], true))
                @can('printing.quotation.apply-estimate')
                    <form method="POST" action="{{ route('admin.printing-intelligence.artwork-analysis.apply-quotation-estimate', [$analysis, $quotationEstimate]) }}" class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-4">
                        @csrf
                        <p class="text-sm text-amber-900">{{ __('This does not change quotation line totals or approval status. It only records advisory estimate fields.') }}</p>
                        @if ($piConfig['require_confirmation_to_apply'])
                            <label class="mt-3 flex items-center gap-2 text-sm text-amber-900">
                                <input type="checkbox" name="confirm_apply" value="1" required class="rounded border-amber-400">
                                {{ __('I confirm applying this advisory estimate to the linked quotation.') }}
                            </label>
                        @endif
                        <button type="submit" class="erp-btn-secondary mt-3 text-xs">{{ __('Apply advisory estimate to quotation') }}</button>
                    </form>
                @endcan
            @endif
        @else
            <p class="text-sm text-slate-600">{{ __('Generate a quotation estimate after ink and production estimates are available.') }}</p>
        @endif
    </x-admin.card>

    @if ($analysis->pages->isNotEmpty())
        <x-admin.card class="mt-6">
            <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Per-page coverage') }}</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs uppercase tracking-wide text-slate-500">
                            <th class="py-2 pr-3">#</th>
                            <th class="py-2 pr-3">{{ __('CMYK %') }}</th>
                            <th class="py-2 pr-3">{{ __('C/M/Y/K') }}</th>
                            <th class="py-2 pr-3">{{ __('White %') }}</th>
                            <th class="py-2 pr-3">{{ __('Transparent %') }}</th>
                            <th class="py-2 pr-3">{{ __('Class') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($analysis->pages as $page)
                            <tr>
                                <td class="py-2 pr-3">{{ $page->page_number }}</td>
                                <td class="py-2 pr-3">{{ $page->cmyk_coverage_percent !== null ? number_format((float) $page->cmyk_coverage_percent, 2) : '—' }}</td>
                                <td class="py-2 pr-3 text-xs">
                                    @if ($page->cyan_coverage_percent !== null)
                                        {{ number_format((float) $page->cyan_coverage_percent, 1) }}/{{ number_format((float) $page->magenta_coverage_percent, 1) }}/{{ number_format((float) $page->yellow_coverage_percent, 1) }}/{{ number_format((float) $page->black_coverage_percent, 1) }}
                                    @else — @endif
                                </td>
                                <td class="py-2 pr-3">{{ $page->white_area_percent !== null ? number_format((float) $page->white_area_percent, 2) : '—' }}</td>
                                <td class="py-2 pr-3">{{ $page->transparent_area_percent !== null ? number_format((float) $page->transparent_area_percent, 2) : '—' }}</td>
                                <td class="py-2 pr-3">{{ $page->coverage_class?->label() ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-admin.card>
    @endif

    @if (! empty($analysis->colour_analysis_raw))
        <x-admin.card class="mt-6">
            <details>
                <summary class="cursor-pointer text-sm font-semibold text-slate-900">{{ __('Raw colour analysis data') }}</summary>
                <pre class="mt-3 overflow-x-auto rounded-md bg-slate-50 p-3 text-xs text-slate-800">{{ json_encode($analysis->colour_analysis_raw, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </details>
        </x-admin.card>
    @endif
</x-admin-layout>
