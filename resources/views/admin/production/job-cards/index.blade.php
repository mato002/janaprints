<x-admin-layout :title="__('Job cards')" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.production.dashboard')], ['label' => __('Job cards')]]">
    <x-admin.page-header :title="__('Production job cards')">
        @can('create', App\Models\Production\ProductionJobCard::class)
            <a href="{{ route('admin.production.job-cards.create') }}" class="erp-btn-primary">{{ __('New job card') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.data-table
        :search-placeholder="__('Search job cards…')"
        export-filename="job-cards"
        :chips="[
            ['id' => 'all', 'label' => __('All')],
            ['id' => 'scheduled', 'label' => __('Scheduled')],
            ['id' => 'in_production', 'label' => __('In Production')],
            ['id' => 'completed', 'label' => __('Completed')],
        ]"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Job card') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Sales order') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($jobCards as $card)
                @php
                    $search = strtolower($card->job_card_number.' '.($card->customer?->company_name ?? '').' '.($card->salesOrder?->order_number ?? '').' '.$card->status->value);
                    $chip = strtolower($card->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search), @js($chip))">
                    <td class="font-medium">{{ $card->job_card_number }}</td>
                    <td>{{ $card->customer?->company_name ?? '—' }}</td>
                    <td class="hidden md:table-cell">{{ $card->salesOrder?->order_number ?? '—' }}</td>
                    <td><x-admin.enum-status-badge :status="$card->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.production.job-cards.show', $card)">{{ __('View') }}</x-admin.table-row-action>
                            @can('update', $card)
                                <x-admin.table-row-action :href="route('admin.production.job-cards.edit', $card)">{{ __('Edit') }}</x-admin.table-row-action>
                            @endcan
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="collection" :title="__('No job cards yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$jobCards" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
