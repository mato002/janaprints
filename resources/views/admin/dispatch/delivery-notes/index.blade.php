<x-admin-layout :title="__('Delivery Notes')" :breadcrumbs="[['label' => __('Dispatch'), 'url' => route('admin.dispatch.dashboard')], ['label' => __('Delivery notes')]]">
    <x-admin.page-header :title="__('Delivery notes')" :description="__('Operational delivery truth — not sales order status.')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.dispatch.delivery-notes.index')" :reset-url="route('admin.dispatch.delivery-notes.index')" :show-reset="false">
            <x-admin.status-pills
                :options="collect(App\Enums\Dispatch\DeliveryNoteStatus::cases())->map(fn ($status) => ['value' => $status->value, 'label' => $status->label()])->prepend(['value' => '', 'label' => __('All')])->all()"
                param="status"
                :current="$filterStatus ?? ''"
            />
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table :searchable="false" :exportable="false">
        <x-slot:head>
            <tr>
                <th>{{ __('DN number') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Job') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Status') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>
        <x-slot:body>
            @forelse ($notes as $note)
                <tr>
                    <td class="font-mono">{{ $note->delivery_note_number }}</td>
                    <td>{{ $note->customer?->company_name }}</td>
                    <td>{{ $note->productionJobCard?->job_card_number ?? '—' }}</td>
                    <td>{{ $note->delivery_date->format('M j, Y') }}</td>
                    <td><x-admin.enum-status-badge :status="$note->status->value" /></td>
                    <td class="erp-table-actions-col">
                        <a href="{{ route('admin.dispatch.delivery-notes.show', $note) }}" class="text-indigo-600">{{ __('View') }}</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No delivery notes.') }}</td></tr>
            @endforelse
        </x-slot:body>
        @if ($notes->hasPages())
            <x-slot:footer><x-admin.table-pagination :paginator="$notes" /></x-slot:footer>
        @endif
    </x-admin.data-table>
</x-admin-layout>
