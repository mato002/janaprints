@if (filled($quotation->estimation_version))
    <x-admin.card class="mb-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="font-medium mb-1">{{ __('Printing Intelligence Estimate') }}</h3>
                <p class="text-xs text-slate-500">{{ __('Advisory costing from Printing Intelligence — does not replace line-item totals above.') }}</p>
            </div>
            @if ($linkedArtworkAnalysis)
                @can('printing.intelligence.view')
                    <a href="{{ route('admin.printing-intelligence.artwork-analysis.show', $linkedArtworkAnalysis) }}" class="erp-btn-secondary text-xs">{{ __('Open in Printing Intelligence') }}</a>
                @endcan
            @endif
        </div>
        <dl class="mt-4 grid grid-cols-2 gap-3 text-sm lg:grid-cols-4">
            <div><dt class="text-xs text-slate-500">{{ __('Estimated total cost') }}</dt><dd>{{ $quotation->estimated_total_cost !== null ? number_format((float) $quotation->estimated_total_cost, 2) : '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Recommended price') }}</dt><dd>{{ $quotation->recommended_price !== null ? number_format((float) $quotation->recommended_price, 2) : '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Confidence') }}</dt><dd>{{ $quotation->confidence_score !== null ? number_format((float) $quotation->confidence_score, 1).'%' : '—' }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Estimation version') }}</dt><dd>{{ $quotation->estimation_version ?? '—' }}</dd></div>
            @if ($appliedQuotationEstimate?->applied_at)
                <div class="md:col-span-2"><dt class="text-xs text-slate-500">{{ __('Last applied') }}</dt><dd>{{ $appliedQuotationEstimate->applied_at->format('Y-m-d H:i') }} @if($appliedQuotationEstimate->appliedByUser) — {{ $appliedQuotationEstimate->appliedByUser->name }} @endif</dd></div>
            @endif
        </dl>
    </x-admin.card>
@endif
