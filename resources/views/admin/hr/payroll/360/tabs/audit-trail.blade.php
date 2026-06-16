<x-admin.data-table export-filename="payroll-audit-trail">
    <x-slot name="head">
        <tr>
            <th>{{ __('When') }}</th>
            <th>{{ __('User') }}</th>
            <th>{{ __('Action') }}</th>
            <th>{{ __('Subject') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($audit_trail as $entry)
            <tr>
                <td class="text-sm text-slate-600">{{ $entry->created_at?->format('M j, Y H:i') }}</td>
                <td>{{ $entry->user?->name ?? __('System') }}</td>
                <td class="font-medium">{{ ucfirst(str_replace('_', ' ', $entry->action)) }}</td>
                <td class="text-sm text-slate-600">{{ class_basename($entry->model_type) }} #{{ $entry->model_id }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-admin.empty-state
                        :title="__('No audit entries yet')"
                        :description="__('Activity for this run and its payslips will appear here.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot>
</x-admin.data-table>
