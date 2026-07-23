@php
    use App\Enums\ProductionJobCardStatus;

    $executionState = $executionState ?? [];
    $primaryAction = $primaryAction ?? null;
    $secondaryActions = $secondaryActions ?? [];
    $workflowPresentation = $workflowPresentation ?? null;
    $dispatchSummary = $dispatchSummary ?? null;

    $stageLabel = $workflowPresentation['phase_label']
        ?? $executionState['phase_label']
        ?? $header['status']?->label()
        ?? '—';

    $stagePhase = $workflowPresentation['phase'] ?? $executionState['phase'] ?? 'other';
    $stageTone = match (true) {
        in_array($stagePhase, ['dispatch'], true) => 'success',
        in_array($stagePhase, ['awaiting_fg_post', 'dispatch_blocked'], true) => 'warning',
        in_array($stagePhase, ['cancelled'], true) => 'danger',
        in_array($stagePhase, ['in_progress', 'qc'], true) => 'info',
        default => 'neutral',
    };

    $productName = $header['product_name'] ?? __('No product');
    $customerName = $header['customer_name'] ?? __('No customer');
    $qtyLabel = isset($header['quantity']) && (float) $header['quantity'] > 0
        ? number_format((float) $header['quantity'], 0)
        : null;
    $dueLabel = $header['due_date']
        ? ($header['due_date']->isToday() ? __('Due today') : $header['due_date']->format('M j, Y'))
        : null;
    $progress = (int) ($header['progress_percent'] ?? 0);

    $dispatchSummaryState = $executionState['dispatch_summary'] ?? $dispatchSummary;
    $hasDispatch = ! empty($dispatchSummaryState['has_delivery_note']);
    $dispatchActions = $dispatchSummaryState['actions'] ?? ['primary' => null, 'secondary' => [], 'danger' => []];
    $workflowNextStep = $executionState['workflow_next_step'] ?? null;

    $heroAction = $hasDispatch
        ? ($dispatchActions['primary'] ?? null)
        : ($workflowNextStep
            ? ['label' => $workflowNextStep['label'], 'type' => 'link', 'url' => $workflowNextStep['url'], 'variant' => 'primary']
            : $primaryAction);
@endphp

<header class="job-360-hero mb-4">
    <div class="job-360-hero__shell">
        <div class="job-360-hero__main">
            <div class="job-360-hero__identity">
                <p class="job-360-hero__eyebrow">
                    <span class="font-mono">{{ $header['job_number'] }}</span>
                    @if ($header['is_delayed'])
                        <span class="job-360-pill job-360-pill--danger">{{ __('Delayed') }}</span>
                    @endif
                    <span class="job-360-pill job-360-pill--neutral">{{ str_replace('_', ' ', $header['priority']->value) }}</span>
                </p>

                <h1 class="job-360-hero__title">{{ $productName }}</h1>
                <p class="job-360-hero__subtitle">{{ $customerName }}</p>

                <div class="job-360-hero__meta">
                    @if ($qtyLabel)
                        <span class="job-360-hero__meta-item">
                            <x-admin.icon name="cube" class="h-4 w-4" />
                            {{ __('Qty :qty', ['qty' => $qtyLabel]) }}
                        </span>
                    @endif
                    @if ($dueLabel)
                        <span class="job-360-hero__meta-item">
                            <x-admin.icon name="calendar" class="h-4 w-4" />
                            {{ $dueLabel }}
                        </span>
                    @endif
                    @if ($header['machine_name'] ?? $header['work_center'] ?? null)
                        <span class="job-360-hero__meta-item hidden sm:inline-flex">
                            <x-admin.icon name="cog" class="h-4 w-4" />
                            {{ $header['machine_name'] ?? $header['work_center'] }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="job-360-hero__status">
                <p class="job-360-hero__status-label">{{ __("Today's action") }}</p>
                <div @class(['job-360-hero__stage', 'job-360-hero__stage--'.$stageTone])>
                    <span class="job-360-hero__stage-dot" aria-hidden="true"></span>
                    <span class="job-360-hero__stage-text">{{ $stageLabel }}</span>
                </div>

                <div class="job-360-hero__progress" aria-label="{{ __('Workflow progress') }}">
                    <div class="job-360-hero__progress-track">
                        <div class="job-360-hero__progress-fill" style="width: {{ $progress }}%"></div>
                    </div>
                    <span class="job-360-hero__progress-value tabular-nums">{{ $progress }}%</span>
                </div>

                <p class="job-360-hero__next-action">{{ $executionState['next_action'] ?? '' }}</p>
            </div>
        </div>

        <div class="job-360-hero__actions">
            <div class="job-360-hero__action-stack">
                @if ($hasDispatch)
                    @if ($dispatchActions['primary'] ?? null)
                        <a href="{{ $dispatchActions['primary']['url'] }}" class="job-360-hero__action erp-btn-primary" data-turbo-frame="erp-main">
                            {{ $dispatchActions['primary']['label'] }}
                        </a>
                    @endif
                    @foreach ($dispatchActions['secondary'] ?? [] as $action)
                        <a
                            href="{{ $action['url'] }}"
                            class="erp-btn-secondary text-sm"
                            @if (($action['target'] ?? null) === '_blank') target="_blank" rel="noopener" @else data-turbo-frame="erp-main" @endif
                        >{{ $action['label'] }}</a>
                    @endforeach
                @else
                    @include('admin.production.job-cards.workspace.partials.primary-action-button', [
                        'action' => $heroAction,
                        'completion' => $completion,
                        'size' => 'lg',
                    ])
                @endif
            </div>

            <details class="job-360-hero__more">
                <summary>{{ __('More actions') }}</summary>
                <div class="job-360-hero__more-menu">
                    <a href="{{ route('admin.production.floor') }}" class="job-360-hero__more-link" data-turbo-frame="erp-main">{{ __('Back to floor') }}</a>
                    @can('update', $jobCard)
                        <a href="{{ route('admin.production.job-cards.edit', $jobCard) }}" class="job-360-hero__more-link" data-turbo-frame="erp-main">{{ __('Edit job') }}</a>
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
                                <form method="POST" action="{{ route('admin.production.job-cards.hold', $jobCard) }}">
                                    @csrf
                                    <button type="submit" class="job-360-hero__more-link">{{ __('Hold job') }}</button>
                                </form>
                            @endif
                            @if ($jobCard->status->canTransitionTo(ProductionJobCardStatus::Cancelled))
                                <form method="POST" action="{{ route('admin.production.job-cards.cancel', $jobCard) }}">
                                    @csrf
                                    <button type="submit" class="job-360-hero__more-link job-360-hero__more-link--danger">{{ __('Cancel job') }}</button>
                                </form>
                            @endif
                        @endcan
                    @endunless
                    @can('delete', $jobCard)
                        <form method="POST" action="{{ route('admin.production.job-cards.destroy', $jobCard) }}" onsubmit="return confirm(@js(__('Permanently delete this job card? This cannot be undone.')))">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="job-360-hero__more-link job-360-hero__more-link--danger">{{ __('Delete job') }}</button>
                        </form>
                    @endcan
                </div>
            </details>
        </div>
    </div>
</header>
