<x-admin-layout :title="$program->title" :breadcrumbs="[['label' => __('HR'), 'url' => route('admin.workspaces.hr')], ['label' => __('Training'), 'url' => route('admin.hr.training.dashboard')], ['label' => $program->title]]">
    <x-admin.page-header :title="$program->title" :description="$program->code">
        <x-slot name="actions">
            <span class="erp-badge bg-slate-100 text-slate-700">{{ $program->status->label() }}</span>
            <a href="{{ route('admin.hr.training.programs.index') }}" class="erp-btn-secondary">{{ __('Programs') }}</a>
            @can('update', $program)
                <a href="{{ route('admin.hr.training.programs.edit', $program) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    @if (session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
    @endif

    <div class="mb-6 grid gap-4 md:grid-cols-4">
        <x-admin.kpi-widget :label="__('Duration')" :value="number_format($program->duration_hours, 1).' '.__('hrs')" icon="clock" />
        <x-admin.kpi-widget :label="__('Budget')" :value="$program->budget_amount ? number_format($program->budget_amount, 0) : '—'" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Assignments')" :value="$program->assignments_count ?? $program->assignments->count()" icon="users" />
        <x-admin.kpi-widget :label="__('Type')" :value="$program->type->label()" icon="book-open" />
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @can('update', $program)
            @if ($program->status === \App\Enums\TrainingProgramStatus::Draft)
                <form method="POST" action="{{ route('admin.hr.training.programs.activate', $program) }}">@csrf<button type="submit" class="erp-btn-primary text-xs">{{ __('Activate') }}</button></form>
            @endif
            @if ($program->status === \App\Enums\TrainingProgramStatus::Active)
                <form method="POST" action="{{ route('admin.hr.training.programs.deactivate', $program) }}">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Deactivate') }}</button></form>
                <form method="POST" action="{{ route('admin.hr.training.programs.complete', $program) }}">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Mark Completed') }}</button></form>
            @endif
            @if (in_array($program->status, [\App\Enums\TrainingProgramStatus::Archived, \App\Enums\TrainingProgramStatus::Cancelled, \App\Enums\TrainingProgramStatus::Completed]))
                <form method="POST" action="{{ route('admin.hr.training.programs.reopen', $program) }}">@csrf<button type="submit" class="erp-btn-primary text-xs">{{ __('Reopen') }}</button></form>
            @endif
            <form method="POST" action="{{ route('admin.hr.training.programs.duplicate', $program) }}">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Duplicate') }}</button></form>
        @endcan
        @can('archive', $program)
            @if ($program->status !== \App\Enums\TrainingProgramStatus::Archived)
                <form method="POST" action="{{ route('admin.hr.training.programs.archive', $program) }}">@csrf<button type="submit" class="erp-btn-secondary text-xs">{{ __('Archive') }}</button></form>
            @endif
        @endcan
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-admin.card :title="__('Program Details')">
            <dl class="space-y-2 text-sm">
                <div><dt class="text-slate-500">{{ __('Description') }}</dt><dd>{{ $program->description ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Schedule') }}</dt><dd>{{ $program->scheduled_start_date?->format('M j, Y') ?? '—' }} — {{ $program->scheduled_end_date?->format('M j, Y') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Certification') }}</dt><dd>{{ $program->requires_certification ? __('Required') : __('Not required') }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Skills') }}</dt><dd>{{ collect($program->skill_tags)->join(', ') ?: '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Evaluation Instructions') }}</dt><dd>{{ $program->evaluation_instructions ?: '—' }}</dd></div>
            </dl>
        </x-admin.card>

        @can('update', $program)
            <x-admin.card :title="__('Training Evaluation')">
                <form method="POST" action="{{ route('admin.hr.training.programs.evaluate', $program) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="erp-label" for="score">{{ __('Score (0–100)') }}</label>
                        <input type="number" id="score" name="score" min="0" max="100" class="erp-input w-full" required>
                    </div>
                    <div>
                        <label class="erp-label" for="feedback">{{ __('Feedback') }}</label>
                        <textarea id="feedback" name="feedback" rows="3" class="erp-input w-full"></textarea>
                    </div>
                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Record Evaluation') }}</button>
                </form>
                @if ($program->evaluations->isNotEmpty())
                    <div class="mt-4 space-y-2 text-sm">
                        @foreach ($program->evaluations->take(5) as $evaluation)
                            <div class="rounded border border-erp-border/60 p-2">
                                <p class="font-medium">{{ $evaluation->score }}/100</p>
                                <p class="text-slate-500">{{ $evaluation->feedback }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-admin.card>
        @endcan
    </div>
</x-admin-layout>
