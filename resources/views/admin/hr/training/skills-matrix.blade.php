<x-admin-layout :title="__('Skills Matrix')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => __('Skills Matrix')]]">
    <x-admin.page-header :title="__('Skills Matrix')" :description="__('Employee skills acquired through completed training.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.training.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    <form method="GET" class="erp-card mb-4 max-w-md">
        <label class="erp-label">{{ __('Employee') }}</label>
        <select name="employee_id" class="erp-input w-full text-sm" onchange="this.form.submit()">
            <option value="">{{ __('All employees') }}</option>
            @foreach ($formData['employees'] as $employee)
                <option value="{{ $employee->id }}" @selected((int) ($filters['employee_id'] ?? 0) === $employee->id)>{{ $employee->full_name }}</option>
            @endforeach
        </select>
    </form>

    <x-admin.data-table export-filename="skills-matrix">
        <x-slot name="head">
            <tr>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Skill') }}</th>
                <th>{{ __('Proficiency') }}</th>
                <th>{{ __('Acquired') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($skills as $skill)
                <tr>
                    <td>{{ $skill->employee->full_name }}</td>
                    <td>{{ $skill->skill_name }}</td>
                    <td>{{ $skill->proficiency->label() }}</td>
                    <td>{{ $skill->acquired_at?->format('Y-m-d') ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="py-6 text-center text-slate-500">{{ __('No skills recorded.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
