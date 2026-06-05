<x-admin-layout :title="__('Training')" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training')]]">
    <x-admin.page-header :title="__('Training & Development')" :description="__('Training programs, certifications, hours, and skills matrix.')">
        <x-slot name="actions">
            @can('create', App\Models\Hr\EmployeeTrainingAssignment::class)
                <a href="{{ route('admin.hr.training.assignments.create') }}" class="erp-btn-primary">{{ __('Assign training') }}</a>
            @endcan
            <a href="{{ route('admin.hr.training.assignments.index') }}" class="erp-btn-secondary">{{ __('All assignments') }}</a>
            <a href="{{ route('admin.hr.training.skills-matrix') }}" class="erp-btn-secondary">{{ __('Skills matrix') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['label' => __('Active Assignments'), 'value' => $stats['active_assignments'], 'icon' => 'book-open'],
            ['label' => __('Completed This Year'), 'value' => $stats['completed_this_year'], 'icon' => 'check-circle'],
            ['label' => __('Training Hours'), 'value' => $stats['total_hours'], 'icon' => 'clock'],
            ['label' => __('Certs Expiring'), 'value' => $stats['expiring_certificates'], 'icon' => 'exclamation'],
        ] as $card)
            <x-admin.kpi-widget :label="$card['label']" :value="$card['value']" :icon="$card['icon']" />
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-2">
        <x-admin.card :title="__('Certificate Expiry Alerts')">
            @if ($expiring->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No certificates expiring in the next 30 days.') }}</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-left text-slate-500">
                                <th class="py-2 pr-3">{{ __('Employee') }}</th>
                                <th class="py-2 pr-3">{{ __('Program') }}</th>
                                <th class="py-2 pr-3">{{ __('Certificate') }}</th>
                                <th class="py-2">{{ __('Expires') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($expiring as $assignment)
                                <tr class="border-b border-slate-100">
                                    <td class="py-2 pr-3">{{ $assignment->employee->full_name }}</td>
                                    <td class="py-2 pr-3">
                                        <a href="{{ route('admin.hr.training.assignments.show', $assignment) }}" class="text-indigo-600 hover:underline">{{ $assignment->program->title }}</a>
                                    </td>
                                    <td class="py-2 pr-3">{{ $assignment->certificate_reference }}</td>
                                    <td class="py-2 text-amber-600">{{ $assignment->certificate_expires_at?->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin.card>

        <x-admin.card :title="__('Skills Matrix Snapshot')">
            @if ($skillsMatrix->isEmpty())
                <p class="text-sm text-slate-500">{{ __('No skills recorded yet. Complete trainings to populate the matrix.') }}</p>
            @else
                <div class="space-y-3 text-sm">
                    @foreach ($skillsMatrix->take(8) as $employeeId => $skills)
                        <div>
                            <p class="font-medium">{{ $skills->first()->employee->full_name }}</p>
                            <p class="text-slate-500">{{ $skills->pluck('skill_name')->join(', ') }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
            <a href="{{ route('admin.hr.training.skills-matrix') }}" class="erp-btn-secondary mt-4 inline-block text-xs">{{ __('View full matrix') }}</a>
        </x-admin.card>
    </div>
</x-admin-layout>
