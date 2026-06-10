<x-admin-layout :title="__('Training Assignments')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => __('Assignments')]]">
    <x-admin.page-header :title="__('Training Assignments')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.training.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\EmployeeTrainingAssignment::class)
                <a href="{{ route('admin.hr.training.assignments.create') }}" class="erp-btn-primary" data-erp-modal-open>{{ __('Assign training') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <select name="employee_id" class="erp-toolbar-select" aria-label="{{ __('Employee') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($formData['employees'] as $employee)
                    <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>{{ $employee->full_name }}</option>
                @endforeach
            </select>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($formData['statuses'] as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

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
