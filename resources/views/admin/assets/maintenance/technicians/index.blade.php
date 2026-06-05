<x-admin-layout :title="__('Technicians')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Technicians')]]">
    <x-admin.page-header :title="__('Maintenance Technicians')" :description="__('Internal and vendor maintenance technicians.')" />
    @can('create', \App\Models\Assets\MaintenanceTechnician::class)
        <x-admin.card class="mb-4">
            <form method="POST" action="{{ route('admin.assets.maintenance.technicians.store') }}" class="grid grid-cols-1 gap-3 sm:grid-cols-3">@csrf
                <input type="text" name="name" class="erp-input" placeholder="{{ __('Name') }}" required>
                <select name="technician_type" class="erp-select" required><option value="internal">{{ __('Internal') }}</option><option value="external">{{ __('External') }}</option></select>
                <input type="text" name="specialization" class="erp-input" placeholder="{{ __('Specialization') }}">
                <button type="submit" class="erp-btn-primary sm:col-span-3">{{ __('Add Technician') }}</button>
            </form>
        </x-admin.card>
    @endcan
    <x-admin.card>
        <table class="erp-table w-full text-sm"><thead><tr><th>{{ __('Name') }}</th><th>{{ __('Type') }}</th><th>{{ __('Specialization') }}</th><th>{{ __('Assigned Orders') }}</th></tr></thead>
        <tbody>@forelse ($technicians as $tech)<tr><td>{{ $tech->name }}</td><td>{{ $tech->technician_type->label() }}</td><td>{{ $tech->specialization ?? '—' }}</td><td>{{ $tech->assigned_work_orders_count }}</td></tr>@empty<tr><td colspan="4" class="py-8 text-center text-slate-500">{{ __('No technicians registered.') }}</td></tr>@endforelse</tbody></table>
        @if ($technicians->hasPages())<div class="mt-4">{{ $technicians->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
