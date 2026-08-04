@php
    use App\Enums\ProductionJobCardStatus;
    use App\Support\Navigation\WorkspaceEmbed;

    $executionState = $executionState ?? [];
    $primaryAction = $primaryAction ?? null;
    $secondaryActions = $secondaryActions ?? [];
    $workflowPresentation = $workflowPresentation ?? null;
    $dispatchSummary = $dispatchSummary ?? null;

    $customerName = $header['customer_name'] ?? __('No customer');
    $qtyLabel = isset($header['quantity']) && (float) $header['quantity'] > 0
        ? number_format((float) $header['quantity'], 0)
        : '—';
    $dueLabel = $header['due_date']
        ? ($header['due_date']->isToday() ? __('Due today') : $header['due_date']->format('M j'))
        : '—';
    $progress = (int) ($header['progress_percent'] ?? 0);
    $stageLabel = $header['current_stage_label']
        ?? $executionState['stage_name']
        ?? $executionState['work_center']
        ?? '—';
    $statusLabel = $header['status']->label();

    $operatorName = $executionState['operator_name'] ?? null;
    $machineName = $executionState['machine_name'] ?? null;
    $needsOperator = (bool) ($executionState['needs_operator'] ?? false);
    $needsMachine = (bool) ($executionState['needs_machine'] ?? false);

    $dispatchSummaryState = $executionState['dispatch_summary'] ?? $dispatchSummary;
    $hasDispatch = ! empty($dispatchSummaryState['has_delivery_note']);
    $dispatchActions = $dispatchSummaryState['actions'] ?? ['primary' => null, 'secondary' => [], 'danger' => []];
    $workflowNextStep = $executionState['workflow_next_step'] ?? null;

    $heroAction = $hasDispatch
        ? ($dispatchActions['primary'] ?? null)
        : ($workflowNextStep
            ? ['label' => $workflowNextStep['label'], 'type' => 'link', 'url' => $workflowNextStep['url'], 'variant' => 'primary']
            : $primaryAction);

    $linkTurboAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
    $formTurboAttrs = WorkspaceEmbed::mainFormAttributes();
@endphp

<header class="job-360-hero mes-header">
    <div class="job-360-hero__shell">
        <div class="job-360-hero__identity">
            <div class="job-360-hero__top-row">
                <span class="job-360-hero__job-number font-mono">{{ $header['job_number'] }}</span>
                @if ($header['is_delayed'])
                    <span class="job-360-pill job-360-pill--danger">{{ __('Delayed') }}</span>
                @endif
                <span class="job-360-pill job-360-pill--neutral">{{ str_replace('_', ' ', $header['priority']->value) }}</span>
                <span class="job-360-pill job-360-pill--neutral">{{ $statusLabel }}</span>
            </div>

            <p class="job-360-hero__meta-line">
                <span class="font-semibold text-slate-800">{{ $customerName }}</span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span>{{ __('Qty') }} {{ $qtyLabel }}</span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span @class(['text-red-700' => $header['is_delayed']])>{{ __('Due') }} {{ $dueLabel }}</span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span>{{ __('Stage') }} {{ $stageLabel }}</span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span @class(['text-amber-800' => $needsOperator])>{{ __('Operator') }} {{ $operatorName ?? '—' }}</span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span @class(['text-amber-800' => $needsMachine])>{{ __('Machine') }} {{ $machineName ?? '—' }}</span>
                <span class="text-slate-300" aria-hidden="true">|</span>
                <span class="job-360-hero__progress-inline">
                    <span class="job-360-hero__progress-track" aria-hidden="true">
                        <span class="job-360-hero__progress-fill" style="width: {{ min(100, max(0, $progress)) }}%"></span>
                    </span>
                    <span class="job-360-hero__progress-value">{{ $progress }}%</span>
                </span>
            </p>
        </div>

        <div class="job-360-hero__actions">
            <div class="job-360-hero__action-row">
                @if ($hasDispatch)
                    @if ($dispatchActions['primary'] ?? null)
                        <a href="{{ $dispatchActions['primary']['url'] }}" class="erp-btn-primary px-3 py-1.5 text-xs" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>
                            {{ $dispatchActions['primary']['label'] }}
                        </a>
                    @endif
                @else
                    @if ($needsOperator)
                        <a href="#assign-operator" class="erp-btn-primary px-3 py-1.5 text-xs">{{ __('Assign operator') }}</a>
                    @endif
                    @if ($needsMachine)
                        <a href="#assign-machine" class="erp-btn-primary px-3 py-1.5 text-xs">{{ __('Assign machine') }}</a>
                    @endif
                    @unless ($needsOperator || $needsMachine)
                        @include('admin.production.job-cards.workspace.partials.primary-action-button', [
                            'action' => $heroAction,
                            'completion' => $completion,
                            'size' => 'sm',
                        ])
                    @endunless
                @endif

                <details class="job-360-hero__more relative">
                    <summary>{{ __('More') }}</summary>
                    <div class="job-360-hero__more-menu absolute right-0 z-40">
                        <a href="{{ route('admin.production.floor') }}" class="job-360-hero__more-link" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>{{ __('Back to floor') }}</a>
                        @can('update', $jobCard)
                            <a href="{{ route('admin.production.job-cards.edit', $jobCard) }}" class="job-360-hero__more-link" @foreach ($linkTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>{{ __('Edit job') }}</a>
                        @endcan
                        @unless ($hasDispatch)
                            @foreach ($secondaryActions as $action)
                                @include('admin.production.job-cards.workspace.partials.primary-action-button', [
                                    'action' => $action,
                                    'completion' => $completion,
                                    'size' => 'sm',
                                ])
                            @endforeach
                            @can('transition', $jobCard)
                                @if ($jobCard->status->canTransitionTo(ProductionJobCardStatus::OnHold)
                                    && $jobCard->status !== ProductionJobCardStatus::InProduction)
                                    <form method="POST" action="{{ route('admin.production.job-cards.hold', $jobCard) }}" @foreach ($formTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>
                                        @csrf
                                        <button type="submit" class="job-360-hero__more-link w-full">{{ __('Hold job') }}</button>
                                    </form>
                                @endif
                                @if ($jobCard->status->canTransitionTo(ProductionJobCardStatus::Cancelled))
                                    <form method="POST" action="{{ route('admin.production.job-cards.cancel', $jobCard) }}" @foreach ($formTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>
                                        @csrf
                                        <button type="submit" class="job-360-hero__more-link job-360-hero__more-link--danger w-full">{{ __('Cancel job') }}</button>
                                    </form>
                                @endif
                            @endcan
                        @endunless
                        @can('delete', $jobCard)
                            <form method="POST" action="{{ route('admin.production.job-cards.destroy', $jobCard) }}" onsubmit="return confirm(@js(__('Permanently delete this job card? This cannot be undone.')))" @foreach ($formTurboAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="job-360-hero__more-link job-360-hero__more-link--danger w-full">{{ __('Delete job') }}</button>
                            </form>
                        @endcan
                    </div>
                </details>
            </div>
        </div>
    </div>
</header>
