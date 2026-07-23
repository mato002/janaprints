@php
    $state = $executionState ?? [];
    $phase = $state['phase'] ?? 'other';
    $primary = $primaryAction ?? null;
    $secondary = $secondaryActions ?? [];
    $machines = $assignableMachines ?? collect();
    $operators = $state['operators'] ?? collect();
    $dispatchSummary = $state['dispatch_summary'] ?? null;
    $hasDispatch = ! empty($dispatchSummary['has_delivery_note']);
    $workflowNextStep = $state['workflow_next_step'] ?? null;
    $summary = $dispatchSummary['summary'] ?? [];
    $dispatchActions = $dispatchSummary['actions'] ?? ['primary' => null, 'secondary' => [], 'danger' => []];
    $showAssignment = ! $hasDispatch && in_array($phase, ['awaiting_operator', 'awaiting_machine', 'ready', 'awaiting_accept', 'queued'], true);
    $showSchedule = ! $hasDispatch && auth()->user()?->can('schedule', $jobCard)
        && in_array($jobCard->status, [
            \App\Enums\ProductionJobCardStatus::Draft,
            \App\Enums\ProductionJobCardStatus::Queued,
            \App\Enums\ProductionJobCardStatus::OnHold,
        ], true)
        && ! $showAssignment;
@endphp

<div class="job-360-execution mb-4 rounded-lg border border-erp-border bg-white">
    <div class="grid gap-4 border-b border-erp-border px-4 py-4 lg:grid-cols-12">
        <div class="lg:col-span-8">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex h-2.5 w-2.5 rounded-full bg-erp-primary"></span>
                <h2 class="text-base font-semibold text-slate-900">{{ $state['phase_label'] ?? $jobCard->status->label() }}</h2>
                @if ($hasDispatch && ! empty($summary['status']))
                    <x-admin.enum-status-badge :status="$summary['status']" />
                @elseif (! empty($state['queue_status']))
                    <span class="erp-badge bg-slate-100 text-slate-700">{{ \App\Enums\ProductionQueueStatus::tryFrom($state['queue_status'])?->label() ?? $state['queue_status'] }}</span>
                @endif
            </div>
            <p class="mt-2 text-sm text-slate-600">{{ $state['next_action'] ?? '' }}</p>

            @if ($hasDispatch)
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Delivery note') }}</dt>
                        <dd class="mt-0.5 font-mono font-medium text-indigo-600">
                            <a href="{{ $summary['show_url'] ?? '#' }}" data-turbo-frame="erp-main">{{ $summary['delivery_note_number'] ?? '—' }}</a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Courier') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $summary['courier'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Tracking') }}</dt>
                        <dd class="mt-0.5 font-mono text-sm font-medium text-slate-900">{{ $summary['tracking_number'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Recipient') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $summary['recipient_name'] ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <dl class="mt-4 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Current stage') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $state['stage_name'] ?? $state['work_center'] ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Queue position') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $state['queue_position'] ? '#'.$state['queue_position'] : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Assigned machine') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $state['machine_name'] ?? __('Not assigned') }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Assigned operator') }}</dt>
                        <dd class="mt-0.5 font-medium text-slate-900">{{ $state['operator_name'] ?? __('Not assigned') }}</dd>
                    </div>
                </dl>
            @endif
        </div>

        <div class="flex flex-col justify-between gap-3 lg:col-span-4">
            <div class="flex flex-wrap items-center justify-end gap-2">
                @if ($hasDispatch)
                    @if ($dispatchActions['primary'] ?? null)
                        <a href="{{ $dispatchActions['primary']['url'] }}" class="erp-btn-primary text-sm" data-turbo-frame="erp-main">{{ $dispatchActions['primary']['label'] }}</a>
                    @endif
                    @foreach ($dispatchActions['secondary'] ?? [] as $action)
                        <a
                            href="{{ $action['url'] }}"
                            class="erp-btn-secondary text-sm"
                            @if (($action['target'] ?? null) === '_blank') target="_blank" rel="noopener" @else data-turbo-frame="erp-main" @endif
                        >{{ $action['label'] }}</a>
                    @endforeach
                    @foreach ($dispatchActions['danger'] ?? [] as $action)
                        <a href="{{ $action['url'] }}" class="text-sm font-medium text-red-600 hover:underline" data-turbo-frame="erp-main">{{ $action['label'] }}</a>
                    @endforeach
                @else
                    @if ($workflowNextStep)
                        <a href="{{ $workflowNextStep['url'] }}" class="erp-btn-primary text-sm" data-turbo-frame="erp-main">{{ $workflowNextStep['label'] }}</a>
                    @endif
                    @if ($primary)
                        @if (($primary['type'] ?? '') === 'post')
                            <form method="POST" action="{{ $primary['url'] }}" class="inline">
                                @csrf
                                <button type="submit" class="{{ ($primary['variant'] ?? '') === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-sm">
                                    {{ $primary['label'] }}
                                </button>
                            </form>
                        @elseif (($primary['type'] ?? '') === 'link' && ! str_contains((string) ($primary['url'] ?? ''), '#assign-'))
                            <a href="{{ $primary['url'] }}" class="{{ ($primary['variant'] ?? '') === 'primary' ? 'erp-btn-primary' : 'erp-btn-secondary' }} text-sm" data-turbo-frame="erp-main">{{ $primary['label'] }}</a>
                        @endif
                    @endif

                    @foreach ($secondary as $action)
                        @if (($action['type'] ?? '') === 'post')
                            <form method="POST" action="{{ $action['url'] }}" class="inline">
                                @csrf
                                <button type="submit" class="erp-btn-secondary text-sm">{{ $action['label'] }}</button>
                            </form>
                        @else
                            <a href="{{ $action['url'] }}" class="erp-btn-secondary text-sm" data-turbo-frame="erp-main">{{ $action['label'] }}</a>
                        @endif
                    @endforeach
                @endif
            </div>

            <details class="text-right text-sm">
                <summary class="cursor-pointer list-none text-slate-500 hover:text-erp-primary [&::-webkit-details-marker]:hidden">
                    {{ __('More actions') }} ▾
                </summary>
                <div class="mt-2 flex flex-wrap justify-end gap-3">
                    @unless ($hasDispatch)
                        @can('transition', $jobCard)
                            @if ($jobCard->status->canTransitionTo(\App\Enums\ProductionJobCardStatus::OnHold)
                                && $jobCard->status !== \App\Enums\ProductionJobCardStatus::InProduction
                                && in_array($phase, ['awaiting_operator', 'awaiting_machine', 'ready', 'queued'], true))
                                <form method="POST" action="{{ route('admin.production.job-cards.hold', $jobCard) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-slate-600 hover:underline">{{ __('Hold') }}</button>
                                </form>
                            @endif
                            @if ($jobCard->status->canTransitionTo(\App\Enums\ProductionJobCardStatus::Cancelled))
                                <form method="POST" action="{{ route('admin.production.job-cards.cancel', $jobCard) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-sm text-red-600 hover:underline">{{ __('Cancel job') }}</button>
                                </form>
                            @endif
                        @endcan
                    @endunless
                    @can('delete', $jobCard)
                        <form method="POST" action="{{ route('admin.production.job-cards.destroy', $jobCard) }}" class="inline" onsubmit="return confirm(@js(__('Permanently delete this job card? This cannot be undone.')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-700 hover:underline">{{ __('Delete job') }}</button>
                        </form>
                    @endcan
                </div>
            </details>
        </div>
    </div>

    @if (($state['needs_operator'] ?? false) && (auth()->user()?->can('schedule', $jobCard) || auth()->user()?->can('update', $jobCard)) && ($state['queue_id'] ?? null))
        <form
            id="assign-operator"
            method="POST"
            action="{{ route('admin.production.job-cards.assign-operator', $jobCard) }}"
            class="flex flex-wrap items-end gap-3 border-b border-erp-border bg-slate-50 px-4 py-3"
        >
            @csrf
            <input type="hidden" name="production_queue_id" value="{{ $state['queue_id'] }}">
            <div class="min-w-[16rem] flex-1">
                <x-admin.lookup-select
                    name="assigned_operator_id"
                    :label="__('Assign operator')"
                    :options="$operators->map(fn ($operator) => ['value' => $operator->id, 'label' => $operator->name])->values()->all()"
                    :required="true"
                    create-route="admin.operators.quick-create"
                    refresh-route="admin.lookups.operators"
                    permission="employees.manage"
                    :modal-title="__('Create operator')"
                    select-class="erp-select w-full text-sm"
                    :placeholder="__('Select operator')"
                />
            </div>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Assign operator') }}</button>
        </form>
    @endif

    @if (($state['needs_machine'] ?? false) && auth()->user()?->can('machines.assign'))
        <form
            id="assign-machine"
            method="POST"
            action="{{ route('admin.production.job-cards.assign-machine', $jobCard) }}"
            class="flex flex-wrap items-end gap-3 border-b border-erp-border bg-slate-50 px-4 py-3"
        >
            @csrf
            <div class="min-w-[16rem] flex-1">
                <label class="block text-[11px] uppercase tracking-wide text-slate-500">{{ __('Assign machine') }}</label>
                    <select name="assigned_machine_asset_id" class="erp-select w-full text-sm" required>
                    <option value="">{{ __('Select machine') }}</option>
                    @foreach ($machines as $machine)
                        <option value="{{ $machine->fixed_asset_id }}">{{ $machine->asset?->asset_name }} ({{ $machine->machine_code }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Assign machine') }}</button>
        </form>
    @endif

    @if ($showSchedule)
        <form method="POST" action="{{ route('admin.production.job-cards.schedule', $jobCard) }}" class="flex flex-wrap items-end gap-2 px-4 py-3">
            @csrf
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-500">{{ __('Planned start') }}</label>
                <input type="date" name="planned_start_date" class="erp-input text-sm py-1" value="{{ $jobCard->planned_start_date?->format('Y-m-d') }}" required>
            </div>
            <div>
                <label class="block text-[11px] uppercase tracking-wide text-slate-500">{{ __('Planned end') }}</label>
                <input type="date" name="planned_end_date" class="erp-input text-sm py-1" value="{{ $jobCard->planned_end_date?->format('Y-m-d') }}" required>
            </div>
            <button type="submit" class="erp-btn-secondary text-sm py-1">{{ __('Update schedule') }}</button>
        </form>
    @endif
</div>

@can('production.outputs.post')
    @php
        $activeTab = $activeTab ?? null;
        $onOutputsTab = $activeTab === 'outputs';
        $completion = $completion ?? ['eligible' => false, 'blockers' => []];
    @endphp
    @unless ($onOutputsTab)
        @include('admin.production.job-cards.workspace.partials.complete-finished-goods-modal', [
            'jobCard' => $jobCard,
            'completion' => $completion,
            'finishedItems' => $finishedItems ?? collect(),
        ])
    @endunless
@endcan
