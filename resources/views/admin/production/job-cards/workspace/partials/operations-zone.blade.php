@php
    use App\Enums\ProductionJobCardStatus;
    use App\Support\Navigation\WorkspaceEmbed;

    $state = $executionState ?? [];
    $machines = $assignableMachines ?? collect();
    $operators = $state['operators'] ?? collect();
    $phase = $state['phase'] ?? 'other';
    $dispatchSummary = $state['dispatch_summary'] ?? null;
    $hasDispatch = ! empty($dispatchSummary['has_delivery_note']);
    $summary = $dispatchSummary['summary'] ?? [];
    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
    $formTurboAttrs = WorkspaceEmbed::mainFormAttributes();

    $showSchedule = ! $hasDispatch && auth()->user()?->can('schedule', $jobCard)
        && in_array($jobCard->status, [
            ProductionJobCardStatus::Draft,
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::OnHold,
        ], true);
@endphp

<section aria-label="{{ __('Production') }}">

    @if ($hasDispatch)
        <dl class="job-360-zone__compact-grid">
            <div><dt>{{ __('Delivery note') }}</dt><dd><a href="{{ $summary['show_url'] ?? '#' }}" class="font-mono text-indigo-700 hover:underline" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>{{ $summary['delivery_note_number'] ?? '—' }}</a></dd></div>
            <div><dt>{{ __('Courier') }}</dt><dd>{{ $summary['courier'] ?? '—' }}</dd></div>
            <div><dt>{{ __('Tracking') }}</dt><dd class="font-mono">{{ $summary['tracking_number'] ?? '—' }}</dd></div>
            <div><dt>{{ __('Recipient') }}</dt><dd>{{ $summary['recipient_name'] ?? '—' }}</dd></div>
        </dl>
    @else
        @if (($state['needs_operator'] ?? false) && (auth()->user()?->can('schedule', $jobCard) || auth()->user()?->can('update', $jobCard)) && ($state['queue_id'] ?? null))
            <form
                id="assign-operator"
                method="POST"
                action="{{ route('admin.production.job-cards.assign-operator', $jobCard) }}"
                class="job-360-zone__assign-row"
                @foreach ($formTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
            >
                @csrf
                <input type="hidden" name="production_queue_id" value="{{ $state['queue_id'] }}">
                <label class="job-360-zone__assign-label">{{ __('Operator') }}</label>
                <div class="job-360-zone__assign-controls">
                    <x-admin.lookup-select
                        name="assigned_operator_id"
                        :options="$operators->map(fn ($operator) => ['value' => $operator->id, 'label' => $operator->name])->values()->all()"
                        :required="false"
                        create-route="admin.operators.quick-create"
                        refresh-route="admin.lookups.operators"
                        permission="employees.manage"
                        :modal-title="__('Create operator')"
                        select-class="erp-select w-full text-sm"
                        :placeholder="__('Select operator (optional)')"
                    />
                    <button type="submit" class="erp-btn-secondary text-sm shrink-0">{{ __('Assign') }}</button>
                </div>
            </form>
        @endif

        @if (($state['needs_machine'] ?? false) && auth()->user()?->can('machines.assign'))
            <form
                id="assign-machine"
                method="POST"
                action="{{ route('admin.production.job-cards.assign-machine', $jobCard) }}"
                class="job-360-zone__assign-row"
                @foreach ($formTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach
            >
                @csrf
                <label class="job-360-zone__assign-label">{{ __('Machine') }}</label>
                <div class="job-360-zone__assign-controls">
                    <select name="assigned_machine_asset_id" class="erp-select w-full text-sm">
                        <option value="">{{ __('Select machine (optional)') }}</option>
                        @foreach ($machines as $machine)
                            <option value="{{ $machine->fixed_asset_id }}">{{ $machine->asset?->asset_name }} ({{ $machine->machine_code }})</option>
                        @endforeach
                    </select>
                    <button type="submit" class="erp-btn-secondary text-sm shrink-0">{{ __('Assign') }}</button>
                </div>
            </form>
        @endif

        <dl class="job-360-zone__compact-grid">
            <div>
                <dt>{{ __('Queue') }}</dt>
                <dd>
                    @if ($state['queue_position'] ?? null)
                        #{{ $state['queue_position'] }}
                        @if ($state['queue_status'] ?? null)
                            · {{ \App\Enums\ProductionQueueStatus::tryFrom($state['queue_status'])?->label() ?? $state['queue_status'] }}
                        @endif
                    @else
                        {{ __('Not queued') }}
                    @endif
                </dd>
            </div>
            <div>
                <dt>{{ __('Stage') }}</dt>
                <dd>{{ $state['stage_name'] ?? $state['work_center'] ?? '—' }}</dd>
            </div>
        </dl>

        @php
            $openOperations = $tabData['open_operations'] ?? collect();
            $canCompleteOp = (bool) ($tabData['can_complete_op'] ?? false);
        @endphp

        @if ($openOperations->isNotEmpty())
            <div id="open-operations" class="mt-3 space-y-2 border-t border-erp-border pt-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Open operations') }}</p>
                <ul class="space-y-2">
                    @foreach ($openOperations as $op)
                        <li class="flex flex-wrap items-center justify-between gap-2 rounded-md bg-slate-50 px-2 py-1.5 text-sm">
                            <span>
                                {{ $op->stage?->name ?? $op->workCenter?->name ?? __('Operation') }}
                                @if ($op->started_at)
                                    <span class="text-slate-500">· {{ __('In progress') }}</span>
                                @else
                                    <span class="text-slate-500">· {{ __('Not started') }}</span>
                                @endif
                            </span>
                            @if ($canCompleteOp)
                                <form method="POST" action="{{ route('admin.production.operations.complete', [$jobCard, $op]) }}" @foreach ($formTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>
                                    @csrf
                                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Complete') }}</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($showSchedule)
            <form method="POST" action="{{ route('admin.production.job-cards.schedule', $jobCard) }}" class="job-360-zone__inline-form" @foreach ($formTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>
                @csrf
                <div class="job-360-zone__form-field">
                    <label>{{ __('Planned start') }}</label>
                    <input type="date" name="planned_start_date" class="erp-input text-sm" value="{{ $jobCard->planned_start_date?->format('Y-m-d') }}" required>
                </div>
                <div class="job-360-zone__form-field">
                    <label>{{ __('Planned end') }}</label>
                    <input type="date" name="planned_end_date" class="erp-input text-sm" value="{{ $jobCard->planned_end_date?->format('Y-m-d') }}" required>
                </div>
                <button type="submit" class="erp-btn-secondary text-sm">{{ __('Update schedule') }}</button>
            </form>
        @endif
    @endif
</section>
