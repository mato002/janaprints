<div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
    <x-admin.kpi-widget :label="__('Total waste cost')" :value="number_format($waste['waste_cost'] ?? 0, 2)" icon="exclamation" />
    <x-admin.kpi-widget :label="__('Waste %')" :value="($waste['waste_percent'] ?? 0).'%'" icon="chart-pie" />
    <x-admin.kpi-widget :label="__('Production waste')" :value="number_format($waste['production_waste_cost'] ?? 0, 2)" icon="cube" />
    <x-admin.kpi-widget :label="__('Serial spoilage')" :value="number_format($waste['serial_spoilage_cost'] ?? 0, 2)" icon="hashtag" />
</div>

<div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
    <x-admin.card>
        <div class="border-b border-erp-border px-4 py-3"><h3 class="text-sm font-semibold">{{ __('Top Waste Reasons') }}</h3></div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Reason') }}</th><th>{{ __('Cost') }}</th><th>{{ __('Qty') }}</th></tr></thead>
                <tbody>
                    @forelse ($waste['top_reasons'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['reason'] ?? '—' }}</td>
                            <td class="font-mono">{{ number_format($row['waste_cost'] ?? 0, 2) }}</td>
                            <td>{{ number_format($row['waste_qty'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-slate-500">{{ __('No waste records.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="border-b border-erp-border px-4 py-3"><h3 class="text-sm font-semibold">{{ __('Waste by Product') }}</h3></div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Product') }}</th><th>{{ __('Cost') }}</th></tr></thead>
                <tbody>
                    @forelse ($waste['by_product'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['product_name'] ?? '—' }}</td>
                            <td class="font-mono">{{ number_format($row['waste_cost'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="py-6 text-center text-slate-500">{{ __('No data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
    <x-admin.card>
        <div class="border-b border-erp-border px-4 py-3"><h3 class="text-sm font-semibold">{{ __('Waste by Branch') }}</h3></div>
        <div class="overflow-x-auto p-4">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Branch') }}</th><th>{{ __('Cost') }}</th></tr></thead>
                <tbody>
                    @forelse ($waste['by_branch'] ?? [] as $row)
                        <tr>
                            <td>{{ $row['branch_name'] ?? '—' }}</td>
                            <td class="font-mono">{{ number_format($row['waste_cost'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="py-6 text-center text-slate-500">{{ __('No data.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</div>
