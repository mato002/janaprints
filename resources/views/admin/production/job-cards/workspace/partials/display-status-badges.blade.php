@php
    use App\Enums\ProductionJobCardStatus;

    $workflowPresentation = $workflowPresentation ?? null;
    $status = $status ?? null;
    $hasPostedOutput = (bool) ($hasPostedOutput ?? false);
    $completion = $completion ?? ['eligible' => false, 'blockers' => []];
    $dispatchSummary = $dispatchSummary ?? null;

    if (! empty($workflowPresentation['badges'])) {
        $badges = $workflowPresentation['badges'];
        $currentStage = $workflowPresentation['current_stage_label'] ?? ($status?->label() ?? '—');
    } else {
        $badges = [];
        $currentStage = $status?->label() ?? '—';

        if ($dispatchSummary['has_delivery_note'] ?? false) {
            $badges[] = ['label' => $dispatchSummary['workflow_label'], 'variant' => match ($dispatchSummary['workflow_phase'] ?? '') {
                'delivered', 'closed' => 'success',
                'dispatched' => 'in_production',
                default => 'neutral',
            }];
            $currentStage = $dispatchSummary['workflow_label'];
        } elseif ($status === ProductionJobCardStatus::ReadyForDispatch && ! $hasPostedOutput) {
            $badges[] = ['label' => __('Production complete'), 'variant' => 'success'];
            $badges[] = ['label' => __('Finished goods pending'), 'variant' => 'warning'];
            $currentStage = __('Finished goods pending');
        } elseif ($status === ProductionJobCardStatus::Completed) {
            $badges[] = ['label' => __('Production complete'), 'variant' => 'success'];
            if (! $hasPostedOutput) {
                $badges[] = ['label' => __('Finished goods pending'), 'variant' => 'warning'];
                $currentStage = __('Finished goods pending');
            } else {
                $currentStage = __('Production complete');
            }
        } elseif ($status === ProductionJobCardStatus::ReadyForDispatch && $hasPostedOutput) {
            $badges[] = ['label' => __('Ready for dispatch'), 'variant' => 'success'];
            $currentStage = __('Ready for dispatch');
        } else {
            $badges[] = ['label' => $status?->label() ?? '—', 'variant' => match ($status) {
                ProductionJobCardStatus::InProduction, ProductionJobCardStatus::QualityCheck => 'in_production',
                ProductionJobCardStatus::Cancelled => 'danger',
                default => 'neutral',
            }];
            $currentStage = $status?->label() ?? '—';
        }
    }
@endphp

@foreach ($badges as $badge)
    <x-admin.status-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-admin.status-badge>
@endforeach

@if ($currentStage)
    <span class="text-xs text-slate-500">
        <span class="uppercase tracking-wide">{{ __('Stage') }}</span>
        <span class="font-semibold text-slate-800">{{ $currentStage }}</span>
    </span>
@endif
