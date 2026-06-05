<x-admin-layout :title="__('Training Programs')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => __('Programs')]]">
    <x-admin.page-header :title="__('Training Programs')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.training.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\EmployeeTrainingAssignment::class)
                <a href="{{ route('admin.hr.training.programs.create') }}" class="erp-btn-primary">{{ __('New program') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <x-admin.data-table :search-placeholder="__('Search programs…')" export-filename="training-programs">
        <x-slot name="head">
            <tr>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Hours') }}</th>
                <th>{{ __('Certification') }}</th>
                <th>{{ __('Skills') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($programs as $program)
                <tr>
                    <td class="font-medium">{{ $program->title }}</td>
                    <td>{{ $program->type->label() }}</td>
                    <td>{{ number_format($program->duration_hours, 1) }}</td>
                    <td>{{ $program->requires_certification ? __('Yes') : __('No') }}</td>
                    <td>{{ collect($program->skill_tags)->join(', ') ?: '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-slate-500">{{ __('No training programs found.') }}</td>
                </tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $programs->links() }}</div>
</x-admin-layout>
