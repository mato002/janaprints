<x-admin-layout :title="__('Complaints')" :breadcrumbs="[['label' => __('Commercial')], ['label' => __('Complaints')]]">
    <x-admin.page-header :title="__('Customer complaints')" :description="__('Track and resolve customer complaints.')">
        <x-slot name="actions">
            @can('create', App\Models\Commercial\CommercialComplaint::class)
                <a href="{{ route('admin.commercial.complaints.create') }}" class="erp-btn-primary">{{ __('Log complaint') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <th>{{ __('Subject') }}</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Priority') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Assigned') }}</th>
            <th>{{ __('Created') }}</th>
            <th></th>
        </x-slot>
        <x-slot name="body">
            @forelse ($complaints as $complaint)
                <tr>
                    <td class="font-medium">{{ $complaint->subject }}</td>
                    <td>{{ $complaint->customer?->company_name ?? '—' }}</td>
                    <td>{{ $complaint->priority->label() }}</td>
                    <td>{{ $complaint->status->label() }}</td>
                    <td>{{ $complaint->assignee?->name ?? '—' }}</td>
                    <td>{{ $complaint->created_at?->format('d M Y') }}</td>
                    <td><a href="{{ route('admin.commercial.complaints.show', $complaint) }}" class="erp-btn-secondary text-xs">{{ __('View') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-slate-500">{{ __('No complaints yet.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
    <div class="mt-4">{{ $complaints->links() }}</div>
</x-admin-layout>
