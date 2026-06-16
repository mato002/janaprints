<x-admin-layout :title="$assignment->reference" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => $assignment->reference]]">
    <x-admin.page-header :title="$assignment->reference" :description="$assignment->employee->full_name.' · '.$assignment->program->title">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.training.assignments.index') }}" class="erp-btn-secondary">{{ __('Back') }}</a>
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        <x-admin.card :title="__('Assignment Details')">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-slate-500">{{ __('Employee') }}</dt><dd>{{ $assignment->employee->full_name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Program') }}</dt><dd>{{ $assignment->program->title }} ({{ $assignment->program->type->label() }})</dd></div>
                <div><dt class="text-slate-500">{{ __('Status') }}</dt><dd>{{ $assignment->status->label() }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Hours Completed') }}</dt><dd>{{ number_format($assignment->hours_completed, 1) }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Due Date') }}</dt><dd>{{ $assignment->due_date?->format('Y-m-d') ?? '—' }}</dd></div>
                @if ($assignment->certificate_reference)
                    <div><dt class="text-slate-500">{{ __('Certificate') }}</dt><dd>{{ $assignment->certificate_reference }}</dd></div>
                @endif
                @if ($assignment->certificate_expires_at)
                    <div>
                        <dt class="text-slate-500">{{ __('Certificate Expiry') }}</dt>
                        <dd @class(['text-rose-600' => $assignment->isCertificateExpired(), 'text-amber-600' => $assignment->isCertificateExpiringSoon()])>
                            {{ $assignment->certificate_expires_at->format('Y-m-d') }}
                        </dd>
                    </div>
                @endif
            </dl>
        </x-admin.card>

        @if ($assignment->skills->isNotEmpty())
            <x-admin.card :title="__('Skills Acquired')">
                <ul class="space-y-2 text-sm">
                    @foreach ($assignment->skills as $skill)
                        <li>{{ $skill->skill_name }} — {{ $skill->proficiency->label() }}</li>
                    @endforeach
                </ul>
            </x-admin.card>
        @endif
    </div>

    @can('update', $assignment)
        @if ($assignment->status === \App\Enums\TrainingAssignmentStatus::Assigned)
            <x-admin.card class="mt-4" :title="__('Start Training')">
                <form method="POST" action="{{ route('admin.hr.training.assignments.start', $assignment) }}">
                    @csrf
                    <button type="submit" class="erp-btn-primary">{{ __('Start training') }}</button>
                </form>
            </x-admin.card>
        @endif

        @if (in_array($assignment->status, [\App\Enums\TrainingAssignmentStatus::Assigned, \App\Enums\TrainingAssignmentStatus::InProgress]))
            <x-admin.card class="mt-4" :title="__('Cancel Assignment')">
                <form method="POST" action="{{ route('admin.hr.training.assignments.cancel', $assignment) }}" onsubmit="return confirm(@js(__('Cancel this training assignment?')))">
                    @csrf
                    <button type="submit" class="erp-btn-secondary text-red-600">{{ __('Cancel training') }}</button>
                </form>
            </x-admin.card>
        @endif

        @if ($assignment->status->value !== 'completed' && $assignment->status->value !== 'cancelled')
            <x-admin.card class="mt-4" :title="__('Mark Complete')">
                <form method="POST" action="{{ route('admin.hr.training.assignments.complete', $assignment) }}" class="max-w-2xl">
                    @csrf
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="erp-label" for="hours_completed">{{ __('Hours Completed') }}</label>
                            <input id="hours_completed" type="number" step="0.5" name="hours_completed" value="{{ $assignment->program->duration_hours }}" class="erp-input w-full">
                        </div>
                        @if ($assignment->program->requires_certification)
                            <div>
                                <label class="erp-label" for="certificate_reference">{{ __('Certificate Reference') }}</label>
                                <input id="certificate_reference" type="text" name="certificate_reference" class="erp-input w-full">
                            </div>
                            <div>
                                <label class="erp-label" for="certificate_expires_at">{{ __('Certificate Expiry') }}</label>
                                <input id="certificate_expires_at" type="date" name="certificate_expires_at" class="erp-input w-full">
                            </div>
                        @endif
                        <div class="md:col-span-2">
                            <label class="erp-label" for="notes">{{ __('Completion Notes') }}</label>
                            <textarea id="notes" name="notes" rows="2" class="erp-input w-full"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="erp-btn-primary mt-4">{{ __('Complete training') }}</button>
                </form>
            </x-admin.card>
        @endif
    @endcan
</x-admin-layout>
