<x-admin-layout
    :title="__('Job Profitability')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Job Costing & Profitability')],
    ]"
>
    <x-admin.page-header
        :title="__('Job Profitability Command Center')"
        :description="__('Decision-focused profitability intelligence from existing job costing data — revenue, margins, cost drivers, and alerts.')"
    >
        @if ($dashboard['has_export_route'] ?? false)
            <x-slot name="actions">
                <a href="{{ route('admin.production.costing.export', request()->query()) }}" class="erp-btn-secondary">
                    {{ __('Export') }}
                </a>
            </x-slot>
        @endif
    </x-admin.page-header>

    <p class="mb-4 text-xs text-slate-500">
        {{ __('As of') }} {{ $dashboard['as_of'] ?? now()->format('Y-m-d H:i') }}
        @if (($dashboard['totals']['job_count'] ?? 0) > 0)
            · {{ __(':count jobs in scope', ['count' => $dashboard['totals']['job_count']]) }}
        @endif
    </p>

    {{-- Section 10: Filters --}}
    <div class="mb-4">
        @include('admin.production.costing.command-center.filters')
    </div>

    {{-- Section 1: Executive KPI strip --}}
    <div class="mb-4">
        @include('admin.production.costing.command-center.kpi-strip')
    </div>

    {{-- Section 2: Profitability health panel --}}
    <div class="mb-6">
        @include('admin.production.costing.command-center.health-panel')
    </div>

    {{-- Sections 3 & 4: Top profitable vs loss-making (two-column) --}}
    <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-2">
        @include('admin.production.costing.command-center.top-profitable')
        @include('admin.production.costing.command-center.loss-making')
    </div>

    {{-- Section 5: Product / service profitability --}}
    <div class="mb-6">
        @include('admin.production.costing.command-center.product-profitability')
    </div>

    {{-- Section 6: Customer profitability --}}
    <div class="mb-6">
        @include('admin.production.costing.command-center.customer-profitability')
    </div>

    {{-- Section 7: Branch profitability --}}
    <div class="mb-6">
        @include('admin.production.costing.command-center.branch-profitability')
    </div>

    {{-- Sections 8 & 9: Cost drivers + alerts --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">
        @include('admin.production.costing.command-center.cost-drivers')
        @include('admin.production.costing.command-center.alerts')
    </div>
</x-admin-layout>
