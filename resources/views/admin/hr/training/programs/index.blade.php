<x-admin-layout :title="__('Training Programs')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => __('Programs')]]">
    <x-admin.page-header :title="__('Training Programs')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.training.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
            @can('create', App\Models\Hr\TrainingProgram::class)
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
                <th>{{ __('Code') }}</th>
                <th>{{ __('Title') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Hours') }}</th>
                <th>{{ __('Assignments') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($programs as $program)
                <tr>
                    <td class="text-slate-500">{{ $program->code ?? '—' }}</td>
                    <td class="font-medium">
                        <a href="{{ route('admin.hr.training.programs.show', $program) }}" class="text-erp-primary hover:underline">{{ $program->title }}</a>
                    </td>
                    <td>{{ $program->type->label() }}</td>
                    <td>{{ $program->status?->label() ?? '—' }}</td>
                    <td>{{ number_format($program->duration_hours, 1) }}</td>
                    <td>{{ $program->assignments_count ?? 0 }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-slate-500">{{ __('No training programs found.') }}</td>
                </tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    <div class="mt-4">{{ $programs->links() }}</div>
</x-admin-layout>
