<x-admin.card class="mb-4">
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Issued Assets') }} ({{ $assets['issued']->count() }})</h3>
    <x-admin.data-table>
        <x-slot name="head"><tr><th>{{ __('Asset') }}</th><th>{{ __('Category') }}</th><th>{{ __('Custody') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($assets['issued'] as $asset)
                <tr>
                    <td>{{ $asset->asset_name }} <span class="text-xs text-slate-500">({{ $asset->asset_number }})</span></td>
                    <td>{{ $asset->category?->name ?? '—' }}</td>
                    <td>{{ $asset->custody_status?->label() ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="3"><x-admin.empty-state :title="__('No assets issued')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin.card>

<x-admin.card>
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Handovers & Returns') }}</h3>
    <x-admin.data-table>
        <x-slot name="head"><tr><th>{{ __('Asset') }}</th><th>{{ __('From') }}</th><th>{{ __('To') }}</th><th>{{ __('Date') }}</th><th>{{ __('Status') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($assets['handovers'] as $handover)
                <tr>
                    <td>{{ $handover->asset?->asset_name }}</td>
                    <td>{{ $handover->fromEmployee?->full_name ?? '—' }}</td>
                    <td>{{ $handover->toEmployee?->full_name ?? '—' }}</td>
                    <td>{{ $handover->handover_date?->format('M j, Y') }}</td>
                    <td>{{ $handover->status?->label() ?? $handover->status }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state :title="__('No handover history')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin.card>
