<x-admin.data-table :search-placeholder="__('Search returns…')" export-filename="asset-returns">
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Asset') }}</th>
            <th scope="col">{{ __('Return date') }}</th>
            <th scope="col">{{ __('Condition') }}</th>
            <th scope="col">{{ __('Received by') }}</th>
            <th scope="col">{{ __('Review') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($returns as $return)
            @php
                $search = strtolower(($return->asset?->asset_number ?? '').' '.($return->asset?->asset_name ?? '').' '.($return->condition->value ?? '').' '.($return->receiver?->name ?? ''));
            @endphp
            <tr x-show="rowVisible(@js($search))">
                <td>
                    <span class="font-medium">{{ $return->asset?->asset_number }}</span>
                    <span class="text-slate-500"> — {{ $return->asset?->asset_name }}</span>
                </td>
                <td class="whitespace-nowrap">{{ $return->return_date?->format('Y-m-d') }}</td>
                <td>{{ $return->condition->label() }}</td>
                <td>{{ $return->receiver?->name ?? '—' }}</td>
                <td>{{ $return->requires_review ? __('Yes') : __('No') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-admin.empty-state icon="clipboard-list" :title="__('No returns yet')" :description="__('Record a return when an assigned asset comes back.')">
                        @can('create', \App\Models\Assets\AssetReturn::class)
                            <x-slot name="action">
                                <x-admin.form-modal-link :href="route('admin.assets.custody.returns.create')">{{ __('New return') }}</x-admin.form-modal-link>
                            </x-slot>
                        @endcan
                    </x-admin.empty-state>
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$returns" /></x-slot>
</x-admin.data-table>
