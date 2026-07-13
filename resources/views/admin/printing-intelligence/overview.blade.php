<x-admin-layout :title="__('Printing Intelligence')" :breadcrumbs="[
    ['label' => __('Printing Intelligence'), 'url' => route('admin.printing-intelligence.overview')],
    ['label' => __('Overview')],
]">
    <x-admin.page-header
        :title="__('Printing Intelligence')"
        :description="__('Unified operational intelligence across artwork, costing, profitability, and forecasting (PI1–PI9). Read-only dashboards.')"
    />

    @include('admin.printing-intelligence.partials.nav')

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 mb-6">
        @foreach ([
            ['label' => __('Artwork Analyses'), 'value' => $metrics['artwork_analyses'] ?? 0, 'icon' => 'photograph', 'route' => 'admin.printing-intelligence.artwork-analysis.index'],
            ['label' => __('Ink Estimates'), 'value' => $metrics['ink_estimates'] ?? 0, 'icon' => 'color-swatch', 'route' => 'admin.printing-intelligence.ink'],
            ['label' => __('Machine Estimates'), 'value' => $metrics['machine_estimates'] ?? 0, 'icon' => 'cog', 'route' => 'admin.printing-intelligence.machines'],
            ['label' => __('Quotation Estimates'), 'value' => $metrics['quotation_estimates'] ?? 0, 'icon' => 'document-text', 'route' => 'admin.printing-intelligence.quotations'],
            ['label' => __('Estimate Accuracy'), 'value' => ($metrics['estimate_accuracy'] ?? null) !== null ? number_format((float) $metrics['estimate_accuracy'], 1).'%' : '—', 'icon' => 'check-circle', 'route' => 'admin.printing-intelligence.estimate-vs-actual', 'permission' => 'printing.estimate-actual.view'],
            ['label' => __('Total Profit (90d)'), 'value' => ($metrics['total_profit'] ?? null) !== null ? number_format((float) $metrics['total_profit'], 2) : '—', 'icon' => 'chart-bar', 'route' => 'admin.printing-intelligence.production-profitability', 'permission' => 'printing.profitability.view'],
            ['label' => __('Average Margin'), 'value' => ($metrics['average_margin'] ?? null) !== null ? number_format((float) $metrics['average_margin'], 1).'%' : '—', 'icon' => 'percent', 'route' => 'admin.printing-intelligence.production-profitability', 'permission' => 'printing.profitability.view'],
            ['label' => __('Forecast Confidence'), 'value' => ($metrics['forecast_confidence'] ?? null) !== null ? number_format((float) $metrics['forecast_confidence'], 1).'%' : '—', 'icon' => 'scale', 'route' => 'admin.printing-intelligence.executive-intelligence', 'permission' => 'printing.executive.view'],
        ] as $card)
            @if (empty($card['permission']) || auth()->user()?->can($card['permission']))
                <a href="{{ route($card['route']) }}" class="block rounded-lg border border-slate-200 bg-white p-3 hover:border-sky-300 transition-colors">
                    <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
                </a>
            @endif
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8 mb-6">
        @foreach ([
            ['label' => __('Materials tracked'), 'value' => $metrics['materials_tracked'], 'icon' => 'cube'],
            ['label' => __('Ink profiles'), 'value' => $metrics['ink_profiles'], 'icon' => 'color-swatch'],
            ['label' => __('Machine profiles'), 'value' => $metrics['machine_profiles'], 'icon' => 'cog'],
            ['label' => __('Items with cost data'), 'value' => $metrics['items_with_cost_data'], 'icon' => 'currency-dollar'],
            ['label' => __('Items with velocity'), 'value' => $metrics['items_with_velocity_data'], 'icon' => 'switch-horizontal'],
            ['label' => __('Stockout risk items'), 'value' => $metrics['items_at_stockout_risk'], 'icon' => 'exclamation'],
            ['label' => __('Dead stock value'), 'value' => number_format($metrics['dead_stock_value'], 2), 'icon' => 'archive'],
            ['label' => __('Avg inventory health'), 'value' => $metrics['average_inventory_health'] !== null ? $metrics['average_inventory_health'].'%' : '—', 'icon' => 'chart-bar'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Quick links') }}</h3>
        <div class="flex flex-wrap gap-2">
            @foreach ([
                ['label' => __('Artwork Analysis'), 'route' => 'admin.printing-intelligence.artwork-analysis.index'],
                ['label' => __('Machine Intelligence'), 'route' => 'admin.printing-intelligence.machines'],
                ['label' => __('Ink Intelligence'), 'route' => 'admin.printing-intelligence.ink'],
                ['label' => __('Material Intelligence'), 'route' => 'admin.printing-intelligence.material'],
                ['label' => __('Cost Intelligence'), 'route' => 'admin.printing-intelligence.cost'],
                ['label' => __('Quotation Intelligence'), 'route' => 'admin.printing-intelligence.quotations'],
            ] as $link)
                <a href="{{ route($link['route']) }}" class="erp-btn-secondary text-xs">{{ $link['label'] }}</a>
            @endforeach
            @can('printing.estimate-actual.view')
                <a href="{{ route('admin.printing-intelligence.estimate-vs-actual') }}" class="erp-btn-secondary text-xs">{{ __('Estimate vs Actual') }}</a>
            @endcan
            @can('printing.calibration.view')
                <a href="{{ route('admin.printing-intelligence.calibration-governance') }}" class="erp-btn-secondary text-xs">{{ __('Calibration Governance') }}</a>
            @endcan
            @can('printing.profitability.view')
                <a href="{{ route('admin.printing-intelligence.production-profitability') }}" class="erp-btn-secondary text-xs">{{ __('Production Profitability') }}</a>
            @endcan
            @can('printing.executive.view')
                <a href="{{ route('admin.printing-intelligence.executive-intelligence') }}" class="erp-btn-secondary text-xs">{{ __('Executive Intelligence') }}</a>
            @endcan
            @can('printing.advisor.view')
                <a href="{{ route('admin.printing-intelligence.operations-advisor') }}" class="erp-btn-secondary text-xs">{{ __('Operations Advisor') }}</a>
            @endcan
            @can('printing.ink-profiles.view')
                <a href="{{ route('admin.printing-intelligence.ink-profiles.index') }}" class="erp-btn-secondary text-xs">{{ __('Ink Profiles') }}</a>
            @endcan
            @can('printing.intelligence.configure')
                <a href="{{ route('admin.printing-intelligence.configuration') }}" class="erp-btn-secondary text-xs">{{ __('Configuration') }}</a>
            @endcan
        </div>
        <p class="mt-4 text-xs text-slate-500">{{ __('Read-only intelligence — no inventory, accounting, or production mutations from this workspace.') }}</p>
    </x-admin.card>
</x-admin-layout>
