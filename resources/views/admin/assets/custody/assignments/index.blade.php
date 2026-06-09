<x-admin-layout :title="__('Assignments')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Assignments')]]">
    <x-admin.page-header :title="__('Asset Assignments')" :description="__('Employee and department custody assignments.')" />

    @can('assets.assign')
        <x-admin.card class="mb-4">
            <h3 class="mb-3 text-sm font-semibold">{{ __('New Assignment') }}</h3>
            <form method="POST" action="{{ route('admin.assets.custody.assignments.store') }}" class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Asset') }}</label>
                    <select name="fixed_asset_id" class="erp-select w-full" required>
                        <option value="">{{ __('Select asset…') }}</option>
                        @foreach ($assets as $asset)
                            <option value="{{ $asset->id }}">{{ $asset->asset_number }} — {{ $asset->asset_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Assignment Type') }}</label>
                    <select name="assignment_type" class="erp-select w-full" required>
                        <option value="employee">{{ __('Employee') }}</option>
                        <option value="department">{{ __('Department') }}</option>
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Employee') }}</label>
                    <select name="assigned_to_employee_id" class="erp-select w-full">
                        <option value="">{{ __('Select employee…') }}</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Department') }}</label>
                    <select name="assigned_to_department_id" class="erp-select w-full">
                        <option value="">{{ __('Select department…') }}</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label">{{ __('Expected Return') }}</label>
                    <input type="date" name="expected_return_date" class="erp-input w-full">
                </div>
                <div>
                    <label class="erp-label">{{ __('Reason') }}</label>
                    <input type="text" name="assignment_reason" class="erp-input w-full" maxlength="120">
                </div>
                <div class="md:col-span-2 lg:col-span-3">
                    <button type="submit" class="erp-btn-primary">{{ __('Assign Asset') }}</button>
                </div>
            </form>
        </x-admin.card>
    @endcan

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
