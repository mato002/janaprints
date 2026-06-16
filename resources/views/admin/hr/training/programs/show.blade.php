<x-admin-layout :title="$program->title" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => $program->title]]">
    <x-admin.page-header :title="$program->title" :description="$program->code">
        <x-slot name="actions">
            <span @class([
                'erp-badge',
                'bg-emerald-100 text-emerald-800' => $program->status === \App\Enums\TrainingProgramStatus::Active,
                'bg-slate-100 text-slate-700' => $program->status === \App\Enums\TrainingProgramStatus::Draft,
                'bg-blue-100 text-blue-800' => $program->status === \App\Enums\TrainingProgramStatus::Completed,
                'bg-amber-100 text-amber-800' => $program->status === \App\Enums\TrainingProgramStatus::Archived,
            ])>{{ $program->status->label() }}</span>
            <a href="{{ route('admin.hr.training.programs.index') }}" class="erp-btn-secondary">{{ __('Programs') }}</a>
            @can('update', $program)
                <a href="{{ route('admin.hr.training.programs.edit', $program) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
            @can('create', App\Models\Hr\EmployeeTrainingAssignment::class)
                @if ($program->isAssignable())
                    <a href="{{ route('admin.hr.training.assignments.create', ['training_program_id' => $program->id]) }}" class="erp-btn-primary">{{ __('Assign Employee') }}</a>
                @endif
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-admin.kpi-widget :label="__('Duration')" :value="number_format($program->duration_hours, 1).' '.__('hrs')" icon="clock" />
        <x-admin.kpi-widget :label="__('Budget')" :value="$program->budget_amount ? number_format($program->budget_amount, 0) : '—'" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Assignments')" :value="$stats['assignments_count']" icon="users" />
        <x-admin.kpi-widget :label="__('Completion Rate')" :value="$stats['completion_rate'].'%'" icon="badge-check" />
    </div>

    <div class="mb-6 grid gap-4 md:grid-cols-3">
        <x-admin.kpi-widget :label="__('Completed')" :value="$stats['completed_count']" icon="badge-check" />
        <x-admin.kpi-widget :label="__('Avg. Evaluation')" :value="$stats['evaluation_count'] > 0 ? $stats['average_evaluation_score'].'/100' : '—'" icon="sparkles" />
        <x-admin.kpi-widget :label="__('Certs Expiring')" :value="$stats['expiring_certificates_count']" icon="exclamation" />
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @can('update', $program)
            @if ($program->status === \App\Enums\TrainingProgramStatus::Draft)
                <form method="POST" action="{{ route('admin.hr.training.programs.activate', $program) }}">@csrf<button type="submit" class="erp-btn-primary text-xs">{{ __('Activate') }}</button></form>
            @endif
            @if ($program->status === \App\Enums\TrainingProgramStatus::Active)
                <form method="POST" action="{{ route('admin.hr.training.programs.deactivate', $program) }}">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Deactivate') }}</button></form>
                <form method="POST" action="{{ route('admin.hr.training.programs.complete', $program) }}" onsubmit="return confirm(@js(__('Mark this program as completed?')))">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Mark Completed') }}</button></form>
            @endif
            @if (in_array($program->status, [\App\Enums\TrainingProgramStatus::Archived, \App\Enums\TrainingProgramStatus::Cancelled, \App\Enums\TrainingProgramStatus::Completed]))
                <form method="POST" action="{{ route('admin.hr.training.programs.reopen', $program) }}">@csrf<button type="submit" class="erp-btn-primary text-xs">{{ __('Reopen') }}</button></form>
            @endif
            <form method="POST" action="{{ route('admin.hr.training.programs.duplicate', $program) }}">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Duplicate') }}</button></form>
        @endcan
        @can('archive', $program)
            @if ($program->status !== \App\Enums\TrainingProgramStatus::Archived)
                <form method="POST" action="{{ route('admin.hr.training.programs.archive', $program) }}" onsubmit="return confirm(@js(__('Archive this program? It will no longer be assignable.')))">@csrf<button type="submit" class="erp-btn-secondary text-xs text-slate-600">{{ __('Archive') }}</button></form>
            @endif
        @endcan
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-admin.card :title="__('Program Details')">
            <dl class="space-y-2 text-sm">
                <div><dt class="text-slate-500">{{ __('Code') }}</dt><dd>{{ $program->code ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ $program->type->label() }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Description') }}</dt><dd>{{ $program->description ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Schedule') }}</dt><dd>{{ $program->scheduled_start_date?->format('M j, Y') ?? '—' }} — {{ $program->scheduled_end_date?->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Certification') }}</dt><dd>{{ $program->requires_certification ? __('Required') : __('Not required') }}@if ($program->requires_certification && $program->certificate_validity_days) ({{ $program->certificate_validity_days }} {{ __('days validity') }})@endif</dd></div>
                <div><dt class="text-slate-500">{{ __('Skills Covered') }}</dt><dd>{{ collect($program->skill_tags)->join(', ') ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Evaluation Instructions') }}</dt><dd>{{ $program->evaluation_instructions ?: '—' }}</dd></div>
            </dl>
        </x-admin.card>

        @can('update', $program)
            <x-admin.card :title="__('Training Evaluation')">
                @if ($program->evaluation_instructions)
                    <p class="mb-3 text-xs text-slate-500">{{ $program->evaluation_instructions }}</p>
                @endif
                <form method="POST" action="{{ route('admin.hr.training.programs.evaluate', $program) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="erp-label" for="score">{{ __('Score (0–100)') }} <span class="text-red-500">*</span></label>
                        <input type="number" id="score" name="score" min="0" max="100" class="erp-input w-full @error('score') border-red-500 @enderror" required>
                        @error('score')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="erp-label" for="feedback">{{ __('Comments') }}</label>
                        <textarea id="feedback" name="feedback" rows="3" class="erp-input w-full @error('feedback') border-red-500 @enderror"></textarea>
                        @error('feedback')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Record Evaluation') }}</button>
                </form>
                @if ($program->evaluations->isNotEmpty())
                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p class="mb-2 text-xs font-medium text-slate-500">{{ __('Recent Feedback') }} ({{ $stats['evaluation_count'] }})</p>
                        <div class="space-y-2 text-sm">
                            @foreach ($program->evaluations as $evaluation)
                                <div class="rounded border border-erp-border/60 p-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <p class="font-medium">{{ $evaluation->score }}/100</p>
                                        <p class="text-xs text-slate-400">{{ $evaluation->evaluated_at?->format('M j, Y') }}</p>
                                    </div>
                                    @if ($evaluation->feedback)
                                        <p class="text-slate-500">{{ $evaluation->feedback }}</p>
                                    @endif
                                    @if ($evaluation->evaluatedBy)
                                        <p class="mt-1 text-xs text-slate-400">{{ $evaluation->evaluatedBy->name }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-admin.card>
        @endcan
    </div>
</x-admin-layout>
