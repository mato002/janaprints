@php
    use App\Enums\ProductionJobCardStatus;

    $state = $executionState ?? [];
    $machines = $assignableMachines ?? collect();
    $operators = $state['operators'] ?? collect();
    $phase = $state['phase'] ?? 'other';
    $dispatchSummary = $state['dispatch_summary'] ?? null;
    $hasDispatch = ! empty($dispatchSummary['has_delivery_note']);
    $summary = $dispatchSummary['summary'] ?? [];

    $showAssignment = ! $hasDispatch && in_array($phase, ['awaiting_operator', 'awaiting_machine', 'ready', 'awaiting_accept', 'queued'], true);
    $showSchedule = ! $hasDispatch && auth()->user()?->can('schedule', $jobCard)
        && in_array($jobCard->status, [
            ProductionJobCardStatus::Draft,
            ProductionJobCardStatus::Queued,
            ProductionJobCardStatus::OnHold,
        ], true)
        && ! $showAssignment;
@endphp

<section class="job-360-zone job-360-zone--operations" aria-label="{{ __('Production') }}">
    <header class="job-360-zone__head">
        <x-admin.icon name="cog" class="h-5 w-5 text-sky-600" />
        <h2 class="job-360-zone__title">{{ __('Production') }}</h2>
    </header>

    @if ($hasDispatch)
        <dl class="job-360-zone__grid">
            <div class="job-360-zone__field">
                <dt>{{ __('Delivery note') }}</dt>
                <dd><a href="{{ $summary['show_url'] ?? '#' }}" class="font-mono text-indigo-700 hover:underline" data-turbo-frame="erp-main">{{ $summary['delivery_note_number'] ?? '—' }}</a></dd>
            </div>
            <div class="job-360-zone__field">
                <dt>{{ __('Courier') }}</dt>
                <dd>{{ $summary['courier'] ?? '—' }}</dd>
            </div>
            <div class="job-360-zone__field">
                <dt>{{ __('Tracking') }}</dt>
                <dd class="font-mono">{{ $summary['tracking_number'] ?? '—' }}</dd>
            </div>
            <div class="job-360-zone__field">
                <dt>{{ __('Recipient') }}</dt>
                <dd>{{ $summary['recipient_name'] ?? '—' }}</dd>
            </div>
        </dl>
    @else
        <dl class="job-360-zone__grid">
            <div class="job-360-zone__field">
                <dt>{{ __('Machine') }}</dt>
                <dd>{{ $state['machine_name'] ?? __('Not assigned') }}</dd>
            </div>
            <div class="job-360-zone__field">
                <dt>{{ __('Operator') }}</dt>
                <dd>{{ $state['operator_name'] ?? __('Not assigned') }}</dd>
            </div>
            <div class="job-360-zone__field">
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
            <div class="job-360-zone__field">
                <dt>{{ __('Current stage') }}</dt>
                <dd>{{ $state['stage_name'] ?? $state['work_center'] ?? '—' }}</dd>
            </div>
        </dl>

        @if (($state['needs_operator'] ?? false) && (auth()->user()?->can('schedule', $jobCard) || auth()->user()?->can('update', $jobCard)) && ($state['queue_id'] ?? null))
            <form
                id="assign-operator"
                method="POST"
                action="{{ route('admin.production.job-cards.assign-operator', $jobCard) }}"
                class="job-360-zone__inline-form"
            >
                @csrf
                <input type="hidden" name="production_queue_id" value="{{ $state['queue_id'] }}">
                <div class="job-360-zone__form-field">
                    <label>{{ __('Assign operator') }}</label>
                    <select name="assigned_operator_id" class="erp-select w-full text-sm" required>
                        <option value="">{{ __('Select operator') }}</option>
                        @foreach ($operators as $operator)
                            <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="erp-btn-primary text-sm">{{ __('Assign operator') }}</button>
            </form>
        @endif

        @if (($state['needs_machine'] ?? false) && auth()->user()?->can('machines.assign'))
            <form
                id="assign-machine"
                method="POST"
                action="{{ route('admin.production.job-cards.assign-machine', $jobCard) }}"
                class="job-360-zone__inline-form"
            >
                @csrf
                <div class="job-360-zone__form-field">
                    <label>{{ __('Assign machine') }}</label>
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
            <form method="POST" action="{{ route('admin.production.job-cards.schedule', $jobCard) }}" class="job-360-zone__inline-form">
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
