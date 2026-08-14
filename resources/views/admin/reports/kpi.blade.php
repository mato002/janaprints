<x-admin-layout :title="$title">
    <x-admin.page-header :title="$title" :description="$description">
        <x-slot name="actions">
            @include('admin.reports.partials.export-button', [
                'can_export' => $can_export,
                'export_route' => 'admin.reports.kpi.export',
                'format_in_path' => true,
            ])
        </x-slot>
    </x-admin.page-header>

    @include('admin.reports.partials.filters', [
        'filters' => $filters,
        'branches' => $branches,
        'showKpiCategory' => true,
    ])

    @forelse ($kpi_groups as $groupKey => $cards)
        <section class="mb-8">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $group_labels[$groupKey] ?? ucfirst($groupKey) }}</h2>
            <div class="erp-kpi-grid">
                @foreach ($cards as $card)
                    <x-admin.card :padding="false" class="erp-kpi relative overflow-hidden">
                        <div class="flex items-start justify-between gap-2 px-3 py-2.5">
                            <div class="min-w-0 flex-1">
                                <p class="text-card-title text-erp-muted">{{ $card['name'] }}</p>
                                <p class="mt-0.5 text-card-value tabular-nums text-erp-primary">{{ $card['value'] }}</p>
                                <p class="mt-0.5 text-[11px] leading-tight text-slate-500">{{ $card['hint'] }}</p>
                                <p class="mt-1 text-[10px] font-medium uppercase tracking-wide text-slate-400">{{ $card['source'] }}</p>
                            </div>
                            <span @class([
                                'shrink-0 rounded px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide',
                                'bg-emerald-50 text-emerald-700' => $card['status'] === 'good',
                                'bg-amber-50 text-amber-700' => $card['status'] === 'watch',
                                'bg-red-50 text-red-700' => $card['status'] === 'critical',
                                'bg-slate-100 text-slate-600' => $card['status'] === 'pending',
                            ])>{{ $card['status_label'] }}</span>
                        </div>
                    </x-admin.card>
                @endforeach
            </div>
        </section>
    @empty
        <x-admin.card>
            <x-admin.empty-state icon="chart-pie" :title="__('No KPIs match filters')" :description="__('Adjust branch or category filters.')" />
        </x-admin.card>
    @endforelse
</x-admin-layout>
