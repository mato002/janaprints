@can('create', \App\Models\Assets\MaintenanceTechnician::class)
    <x-admin.card class="mb-4">
        <form method="POST" action="{{ route('admin.assets.maintenance.technicians.store') }}" class="flex flex-wrap items-end gap-2">
            @csrf
            <input type="hidden" name="tab" value="technicians">
            <input type="text" name="name" class="erp-input min-w-[8rem] flex-1" placeholder="{{ __('Name') }}" required>
            <select name="technician_type" class="erp-select w-auto shrink-0 min-w-[7rem]" required>
                <option value="internal">{{ __('Internal') }}</option>
                <option value="external">{{ __('External') }}</option>
            </select>
            <input type="text" name="specialization" class="erp-input min-w-[8rem] flex-1" placeholder="{{ __('Specialization') }}">
            <button type="submit" class="erp-btn-primary ml-auto shrink-0">{{ __('Add technician') }}</button>
        </form>
    </x-admin.card>
@endcan

<x-admin.data-table
    :search-placeholder="__('Search technicians…')"
    export-filename="maintenance-technicians"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Name') }}</th>
            <th scope="col">{{ __('Type') }}</th>
            <th scope="col">{{ __('Specialization') }}</th>
            <th scope="col">{{ __('Assigned orders') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($technicians as $tech)
            @php
                $search = strtolower($tech->name.' '.($tech->technician_type->value ?? '').' '.($tech->specialization ?? ''));
            @endphp
            <tr x-show="rowVisible(@js($search))">
                <td class="font-medium">{{ $tech->name }}</td>
                <td>{{ $tech->technician_type->label() }}</td>
                <td>{{ $tech->specialization ?? '—' }}</td>
                <td>{{ $tech->assigned_work_orders_count }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-admin.empty-state icon="users" :title="__('No technicians registered')" :description="__('Add a technician above to assign maintenance work orders.')" />
                </td>
            </tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$technicians" /></x-slot>
</x-admin.data-table>
