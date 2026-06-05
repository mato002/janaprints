<x-admin-layout :title="__('Training Assignments')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => __('Assignments')]]">
    <x-admin.page-header :title="__('Training Assignments')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.training.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\EmployeeTrainingAssignment::class)
                <a href="{{ route('admin.hr.training.assignments.create') }}" class="erp-btn-primary">{{ __('Assign training') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <form method="GET" class="erp-card mb-4">
        <div class="grid gap-3 md:grid-cols-3">
            <div>
                <label class="erp-label">{{ __('Employee') }}</label>
                <select name="employee_id" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($formData['employees'] as $employee)
                        <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>{{ $employee->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="erp-label">{{ __('Status') }}</label>
                <select name="status" class="erp-input w-full text-sm">
                    <option value="">{{ __('All') }}</option>
                    @foreach ($formData['statuses'] as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3"><button type="submit" class="erp-btn-primary">{{ __('Filter') }}</button></div>
    </form>

    <x-admin.data-table export-filename="training-assignments">
        <x-slot name="head">
            <tr>
                <th>{{ __('Reference') }}</th>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Program') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Hours') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Certificate Expiry') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($assignments as $assignment)
                <tr>
                    <td>
                        <a href="{{ route('admin.hr.training.assignments.show', $assignment) }}" class="font-medium text-indigo-600 hover:underline">{{ $assignment->reference }}</a>
                    </td>
                    <td>{{ $assignment->employee->full_name }}</td>
                    <td>{{ $assignment->program->title }}</td>
                    <td>{{ $assignment->program->type->label() }}</td>
                    <td>{{ number_format($assignment->hours_completed, 1) }}</td>
                    <td>{{ $assignment->status->label() }}</td>
                    <td>
                        @if ($assignment->certificate_expires_at)
                            <span @class(['text-rose-600' => $assignment->isCertificateExpired(), 'text-amber-600' => $assignment->isCertificateExpiringSoon()])>
                                {{ $assignment->certificate_expires_at->format('Y-m-d') }}
                            </span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-6 text-center text-slate-500">{{ __('No assignments found.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $assignments->links() }}</div>
</x-admin-layout>
