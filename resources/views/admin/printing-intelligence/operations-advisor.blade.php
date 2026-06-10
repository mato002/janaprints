<x-admin-layout :title="__('Operations Advisor')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Operations Advisor')],
]">
    <x-admin.page-header
        :title="__('Operations Advisor')"
        :description="__('Autonomous print operations advisor (PI10). Read-only recommendations — no inventory, accounting, quotation, or production mutations.')"
    />

    @include('admin.printing-intelligence.partials.nav')

    @if (session('status'))
        <x-admin.alert variant="success" class="mb-4">{{ session('status') }}</x-admin.alert>
    @endif

    @php
        $tabs = [
            'overview' => __('Overview'),
            'quotations' => __('Quotations'),
            'artwork' => __('Artwork'),
            'machines' => __('Machines'),
            'inventory' => __('Inventory'),
            'customers' => __('Customers'),
            'profitability' => __('Profitability'),
            'forecasts' => __('Forecasts'),
        ];
        $typeMap = [
            'quotations' => 'quotation',
            'artwork' => 'artwork',
            'machines' => 'machine',
            'inventory' => 'inventory',
            'customers' => 'customer',
            'profitability' => 'profitability',
            'forecasts' => 'forecast',
        ];
        $summary = $overview['summary'] ?? [];
        $confidenceBand = fn ($score) => match (true) {
            $score >= 75 => __('High'),
            $score >= 45 => __('Medium'),
            default => __('Low'),
        };
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <x-admin.card class="flex-1 min-w-0">
            <nav class="flex flex-wrap gap-2">
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('admin.printing-intelligence.operations-advisor', array_merge($filters ?? [], ['tab' => $key])) }}"
                       @class([
                           'rounded-md px-3 py-1.5 text-xs font-medium',
                           'bg-slate-900 text-white' => ($tab ?? 'overview') === $key,
                           'bg-slate-100 text-slate-700 hover:bg-slate-200' => ($tab ?? 'overview') !== $key,
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>
        </x-admin.card>

        @can('printing.advisor.manage')
            <form method="post" action="{{ route('admin.printing-intelligence.advisor.generate') }}">
                @csrf
                <button type="submit" class="erp-btn-secondary text-xs">{{ __('Generate recommendations') }}</button>
            </form>
        @endcan
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5 mb-6">
        <x-admin.kpi-widget :label="__('Open Recommendations')" :value="(string) ($summary['open'] ?? 0)" icon="bell" />
        <x-admin.kpi-widget :label="__('Critical Alerts')" :value="(string) ($summary['critical'] ?? 0)" icon="exclamation" />
        <x-admin.kpi-widget :label="__('High Confidence')" :value="(string) ($summary['high_confidence'] ?? 0)" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Acknowledged')" :value="(string) ($summary['acknowledged'] ?? 0)" icon="clipboard-check" />
        <x-admin.kpi-widget :label="__('Dismissed')" :value="(string) ($summary['dismissed'] ?? 0)" icon="archive" />
    </div>

    @if (($tab ?? 'overview') === 'overview' && ! empty($executiveSummary))
        <div class="grid gap-4 lg:grid-cols-2 mb-6">
            @foreach ([
                'top_opportunities' => __('Top Opportunities'),
                'top_risks' => __('Top Risks'),
                'top_margin_threats' => __('Top Margin Threats'),
                'top_growth_areas' => __('Top Growth Areas'),
                'top_inventory_risks' => __('Top Inventory Risks'),
                'top_capacity_risks' => __('Top Capacity Risks'),
            ] as $key => $heading)
                <x-admin.card>
                    <h3 class="font-medium mb-3">{{ $heading }}</h3>
                    @php $items = $executiveSummary[$key] ?? []; @endphp
                    @if (empty($items))
                        <p class="text-sm text-slate-500">{{ __('No open recommendations in this category.') }}</p>
                    @else
                        <ul class="space-y-2 text-sm">
                            @foreach ($items as $item)
                                <li class="rounded border border-slate-200 px-3 py-2">
                                    <span class="erp-badge mr-2">{{ ucfirst($item->severity?->value ?? 'info') }}</span>
                                    <strong>{{ $item->title }}</strong>
                                    <p class="text-slate-600 mt-1">{{ $item->summary }}</p>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-admin.card>
            @endforeach
        </div>
    @endif

    @php
        $recommendations = ($tab ?? 'overview') === 'overview'
            ? ($overview['recommendations'] ?? collect())
            : collect($liveRecommendations ?? [])->map(fn ($rec) => (object) [
                'id' => null,
                'title' => $rec['title'] ?? '',
                'summary' => $rec['summary'] ?? '',
                'recommendation_text' => $rec['recommendation_text'] ?? '',
                'severity' => \App\Enums\AdvisorSeverity::tryFrom($rec['severity']?->value ?? $rec['severity'] ?? 'info'),
                'recommendation_type' => \App\Enums\AdvisorRecommendationType::tryFrom($rec['recommendation_type']?->value ?? $rec['recommendation_type'] ?? 'quotation'),
                'confidence_score' => $rec['confidence_score'] ?? 0,
                'recommended_action' => $rec['recommended_action'] ?? null,
                'source_module' => $rec['source_module'] ?? '',
                'status' => \App\Enums\AdvisorRecommendationStatus::Open,
                'comment' => null,
            ]);
    @endphp

    <x-admin.card>
        <h3 class="font-medium mb-3">
            @if (($tab ?? 'overview') === 'overview')
                {{ __('Open Recommendations') }}
            @else
                {{ __('Live Advisor Signals') }} — {{ $tabs[$tab] ?? '' }}
            @endif
        </h3>

        @if ($recommendations->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No recommendations yet. Run generation to analyze PI3–PI9 signals.') }}</p>
        @else
            <div class="space-y-3">
                @foreach ($recommendations as $rec)
                    <div class="rounded-lg border border-slate-200 p-4 text-sm">
                        <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                            <div>
                                <span class="erp-badge mr-2">{{ ucfirst($rec->severity?->value ?? 'info') }}</span>
                                <span class="erp-badge mr-2">{{ ucfirst($rec->recommendation_type?->value ?? '') }}</span>
                                <span class="text-xs text-slate-500">{{ $rec->source_module ?? '' }}</span>
                                <h4 class="font-semibold mt-1">{{ $rec->title }}</h4>
                            </div>
                            <div class="text-right text-xs text-slate-500">
                                {{ __('Confidence') }}: {{ number_format((float) ($rec->confidence_score ?? 0), 0) }}
                                ({{ $confidenceBand((float) ($rec->confidence_score ?? 0)) }})
                            </div>
                        </div>
                        <p class="text-slate-700">{{ $rec->summary }}</p>
                        <p class="text-slate-600 mt-2">{{ $rec->recommendation_text }}</p>
                        @if (! empty($rec->recommended_action))
                            <p class="mt-2 text-xs font-medium text-slate-800">{{ __('Recommended action') }}: {{ $rec->recommended_action }}</p>
                        @endif

                        @if ($rec->id && ($rec->status?->value ?? 'open') === 'open')
                            @can('printing.advisor.manage')
                                <div class="mt-3 flex flex-wrap gap-2 border-t border-slate-100 pt-3">
                                    <form method="post" action="{{ route('admin.printing-intelligence.advisor.acknowledge', $rec->id) }}" class="flex flex-wrap items-end gap-2">
                                        @csrf
                                        <input type="text" name="comment" placeholder="{{ __('Comment (optional)') }}" class="erp-input text-xs max-w-xs" />
                                        <button type="submit" class="erp-btn-secondary text-xs">{{ __('Acknowledge') }}</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.printing-intelligence.advisor.dismiss', $rec->id) }}" class="flex flex-wrap items-end gap-2">
                                        @csrf
                                        <input type="text" name="comment" placeholder="{{ __('Dismiss reason') }}" class="erp-input text-xs max-w-xs" />
                                        <button type="submit" class="erp-btn-secondary text-xs">{{ __('Dismiss') }}</button>
                                    </form>
                                </div>
                            @endcan
                        @elseif (! empty($rec->comment))
                            <p class="mt-2 text-xs text-slate-500">{{ __('Comment') }}: {{ $rec->comment }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>
