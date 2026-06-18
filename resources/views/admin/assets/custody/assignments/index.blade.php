<x-admin-layout :title="__('Assignments')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Assignments')]]">
    <x-admin.page-header :title="__('Asset Assignments')" :description="__('Employee and department custody assignments.')">
        <x-slot name="actions">
            @can('assets.assign')
                <x-admin.form-modal-link :href="route('admin.assets.custody.assignments.create')">
                    {{ __('New Assignment') }}
                </x-admin.form-modal-link>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Asset') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Assigned To') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Expected Return') }}</th>
                        <th>{{ __('Assigned At') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr>
                            <td>
                                <a href="{{ route('admin.assets.show', $assignment->asset) }}" class="erp-link">{{ $assignment->asset?->asset_number }}</a>
                                — {{ $assignment->asset?->asset_name }}
                            </td>
                            <td>{{ ucfirst($assignment->assignment_type->value) }}</td>
                            <td>
                                {{ $assignment->assignedEmployee?->full_name
                                    ?? $assignment->assignedDepartment?->name
                                    ?? $assignment->assignedUser?->name
                                    ?? $assignment->assignedBranch?->name
                                    ?? '—' }}
                            </td>
                            <td><x-admin.status-badge :variant="$assignment->status->badgeVariant()">{{ $assignment->status->label() }}</x-admin.status-badge></td>
                            <td>{{ $assignment->expected_return_date?->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $assignment->assigned_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No assignments yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($assignments->hasPages())<div class="mt-4">{{ $assignments->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
