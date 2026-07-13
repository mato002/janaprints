<x-admin-layout :title="__('Delivery Notes')" :breadcrumbs="[['label' => __('Dispatch'), 'url' => route('admin.dispatch.dashboard')], ['label' => __('Delivery notes')]]">
    <x-admin.page-header :title="__('Delivery notes')" :description="__('Operational delivery truth — not sales order status.')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.dispatch.delivery-notes.index')" :reset-url="route('admin.dispatch.delivery-notes.index')">
            <x-admin.status-pills
                :options="collect(App\Enums\Dispatch\DeliveryNoteStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->prepend(['value' => '', 'label' => __('All')])->all()"
                param="status"
                :current="$filterStatus ?? ''"
            />
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :search-placeholder="__('Search delivery notes…')"
        export-filename="delivery-notes"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('DN number') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col">{{ __('Job') }}</th>
                <th scope="col">{{ __('Date') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($notes as $note)
                @php
                    $search = strtolower($note->delivery_note_number.' '.($note->customer?->company_name ?? '').' '.($note->productionJobCard?->job_card_number ?? '').' '.$note->status->value);
                @endphp
                <tr x-show="rowVisible(@js($search))">
                    <td class="font-mono">{{ $note->delivery_note_number }}</td>
                    <td>{{ $note->customer?->company_name ?? '—' }}</td>
                    <td>{{ $note->productionJobCard?->job_card_number ?? '—' }}</td>
                    <td class="whitespace-nowrap">{{ $note->delivery_date->format('M j, Y') }}</td>
                    <td><x-admin.enum-status-badge :status="$note->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.dispatch.delivery-notes.show', $note)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state icon="truck" :title="__('No delivery notes')" :description="__('Delivery notes appear here once jobs are ready for dispatch.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        @if ($notes->hasPages())
            <x-slot name="footer"><x-admin.table-pagination :paginator="$notes" /></x-slot>
        @endif
    </x-admin.data-table>
</x-admin-layout>
