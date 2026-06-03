<x-admin-layout :title="__('Dashboard')" :breadcrumbs="[]">
    <x-admin.page-header
        :title="__('Dashboard')"
        :description="__('Operational overview for :company', ['company' => tenant()->company?->name ?? config('app.name')])"
    />

    {{-- Row 1: KPI widgets --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-admin.kpi-widget
            :label="$dashboard['kpis']['revenue_today']['label']"
            :value="$dashboard['kpis']['revenue_today']['value']"
            :hint="$dashboard['kpis']['revenue_today']['hint']"
            icon="currency-dollar"
        />
        <x-admin.kpi-widget
            :label="$dashboard['kpis']['open_quotes']['label']"
            :value="$dashboard['kpis']['open_quotes']['value']"
            :hint="$dashboard['kpis']['open_quotes']['hint']"
            icon="document-text"
        />
        <x-admin.kpi-widget
            :label="$dashboard['kpis']['jobs_in_production']['label']"
            :value="$dashboard['kpis']['jobs_in_production']['value']"
            :hint="$dashboard['kpis']['jobs_in_production']['hint']"
            icon="cog"
        />
        <x-admin.kpi-widget
            :label="$dashboard['kpis']['receivables']['label']"
            :value="$dashboard['kpis']['receivables']['value']"
            :hint="$dashboard['kpis']['receivables']['hint']"
            icon="cash"
        />
        <x-admin.kpi-widget
            :label="$dashboard['kpis']['stock_alerts']['label']"
            :value="$dashboard['kpis']['stock_alerts']['value']"
            :hint="$dashboard['kpis']['stock_alerts']['hint']"
            icon="archive"
        />
    </div>

    {{-- Row 2: Production pipeline --}}
    <x-admin.card class="mt-6" :padding="false">
        <x-slot name="header">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-section-title text-erp-primary">{{ __('Production Pipeline') }}</h2>
                <p class="text-sm text-slate-500">{{ __('Live counts when production module is enabled') }}</p>
            </div>
        </x-slot>
        <x-slot name="body">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                @foreach ($dashboard['pipeline'] as $stage)
                    <div>
                        <div class="mb-2 flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700">{{ $stage['label'] }}</span>
                            <span class="tabular-nums text-slate-500">{{ $stage['count'] }}</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-erp-page" role="progressbar" aria-valuenow="{{ $stage['percent'] }}" aria-valuemin="0" aria-valuemax="100">
                            <div
                                class="h-full rounded-full bg-erp-accent transition-all duration-500"
                                style="width: {{ max($stage['percent'], $stage['count'] > 0 ? 8 : 0) }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-slot>
    </x-admin.card>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Row 3: Financial snapshot --}}
        <x-admin.card class="lg:col-span-1">
            <h2 class="text-section-title text-erp-primary mb-4">{{ __('Financial Snapshot') }}</h2>
            <dl class="space-y-4">
                <div class="flex items-center justify-between border-b border-erp-border pb-3">
                    <dt class="text-sm text-slate-500">{{ __('Revenue MTD') }}</dt>
                    <dd class="text-lg font-semibold tabular-nums text-erp-primary">{{ $dashboard['financial']['revenue_mtd'] }}</dd>
                </div>
                <div class="flex items-center justify-between border-b border-erp-border pb-3">
                    <dt class="text-sm text-slate-500">{{ __('Expenses MTD') }}</dt>
                    <dd class="text-lg font-semibold tabular-nums text-erp-primary">{{ $dashboard['financial']['expenses_mtd'] }}</dd>
                </div>
                <div class="flex items-center justify-between">
                    <dt class="text-sm text-slate-500">{{ __('Profit MTD') }}</dt>
                    <dd class="text-lg font-semibold tabular-nums text-erp-success">{{ $dashboard['financial']['profit_mtd'] }}</dd>
                </div>
            </dl>
        </x-admin.card>

        {{-- CRM pulse (available data) --}}
        <x-admin.card class="lg:col-span-1">
            <h2 class="text-section-title text-erp-primary mb-4">{{ __('CRM Pulse') }}</h2>
            <div class="grid grid-cols-2 gap-4">
                <x-admin.stat-card :label="__('Open Leads')" :value="$dashboard['crm']['open_leads']" />
                <x-admin.stat-card :label="__('Customers')" :value="$dashboard['crm']['customers']" />
            </div>
            @can('viewAny', App\Models\Crm\Customer::class)
                <div class="mt-4">
                    <x-admin.quick-actions :items="[
                        ['label' => __('New customer'), 'route' => 'admin.crm.customers.create'],
                        ['label' => __('View leads'), 'route' => 'admin.crm.leads.index'],
                    ]" />
                </div>
            @endcan
        </x-admin.card>

        {{-- Context --}}
        <x-admin.card class="lg:col-span-1">
            <h2 class="text-section-title text-erp-primary mb-4">{{ __('Workspace') }}</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500">{{ __('Company') }}</dt>
                    <dd class="mt-0.5 font-medium text-erp-primary">{{ tenant()->company?->name ?? __('Not set') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Branch') }}</dt>
                    <dd class="mt-0.5 font-medium text-erp-primary">{{ tenant()->branch?->name ?? __('All branches') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Role') }}</dt>
                    <dd class="mt-0.5 font-medium text-erp-primary">{{ auth()->user()->getRoleNames()->first() ?? __('None') }}</dd>
                </div>
            </dl>
        </x-admin.card>
    </div>

    {{-- Row 4: Activity timeline --}}
    <x-admin.card class="mt-6" :padding="false">
        <x-slot name="header">
            <div class="flex items-center justify-between px-6 py-4">
                <h2 class="text-section-title text-erp-primary">{{ __('Recent Activity') }}</h2>
                @can('viewAny', App\Models\ActivityLog::class)
                    <a href="{{ route('admin.activity-logs.index') }}" class="text-sm font-medium text-erp-accent hover:underline">{{ __('View all') }}</a>
                @endcan
            </div>
        </x-slot>
        <x-slot name="body">
            <div class="px-6 pb-6">
                <x-admin.activity-timeline :items="$dashboard['recent_activity']" />
            </div>
        </x-slot>
    </x-admin.card>
</x-admin-layout>
