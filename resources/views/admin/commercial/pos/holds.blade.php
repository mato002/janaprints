<x-admin-layout :title="__('Held sales')" :breadcrumbs="[['label' => __('POS'), 'url' => route('admin.commercial.pos.dashboard')], ['label' => __('Held')]]">
    <x-admin.page-header :title="__('Held / suspended sales')" />
    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Held at') }}</th>
                        <th>{{ __('Sale #') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Total') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($holds as $hold)
                        <tr>
                            <td>{{ $hold->held_at->format('Y-m-d H:i') }}</td>
                            <td class="font-mono">{{ $hold->sale?->sale_number }}</td>
                            <td>{{ $hold->customer?->company_name ?? __('Walk-in') }}</td>
                            <td class="tabular-nums">{{ number_format($hold->sale?->total_amount ?? 0, 2) }}</td>
                            <td class="erp-table-actions-col">
                                @can('update', $hold->sale)
                                    <a href="{{ route('admin.commercial.pos.show', $hold->sale) }}" class="text-erp-accent text-sm">{{ __('Resume') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-slate-500">{{ __('No held sales.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-erp-border px-4 py-3"><x-admin.table-pagination :paginator="$holds" /></div>
    </x-admin.card>
</x-admin-layout>
