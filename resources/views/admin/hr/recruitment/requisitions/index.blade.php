<x-admin-layout :title="__('Job Requisitions')">
    <x-admin.page-header :title="__('Job Requisitions')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.recruitment.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\JobRequisition::class)
                <a href="{{ route('admin.hr.recruitment.requisitions.create') }}" class="erp-btn-primary">{{ __('New requisition') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Department') }}</th>
                <th>{{ __('Headcount') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($requisitions as $requisition)
                <tr>
                    <td class="font-mono text-xs">{{ $requisition->reference }}</td>
                    <td><a href="{{ route('admin.hr.recruitment.requisitions.show', $requisition) }}" class="font-medium text-erp-primary hover:underline">{{ $requisition->title }}</a></td>
                    <td>{{ $requisition->department?->name ?? '—' }}</td>
                    <td>{{ $requisition->headcount }}</td>
                    <td><span class="erp-badge bg-slate-100 text-slate-700">{{ $requisition->status->label() }}</span></td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-6 text-center text-slate-500">{{ __('No requisitions found.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
    <div class="mt-4">{{ $requisitions->links() }}</div>
</x-admin-layout>
