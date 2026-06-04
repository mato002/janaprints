<x-admin.card class="mb-6">
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Workflow') }}</h3>
    <div class="flex flex-wrap gap-2">
        @can('schedule', $jobCard)
            @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::Queued))
                <form method="POST" action="{{ route('admin.production.job-cards.queue', $jobCard) }}">@csrf
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Queue') }}</button>
                </form>
            @endif
        @endcan
        @can('start', $jobCard)
            @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::InProduction))
                <form method="POST" action="{{ route('admin.production.job-cards.start', $jobCard) }}">@csrf
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Start production') }}</button>
                </form>
            @endif
        @endcan
        @can('complete', $jobCard)
            @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::QualityCheck))
                <form method="POST" action="{{ route('admin.production.job-cards.send-to-qc', $jobCard) }}">@csrf
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('Send to QC') }}</button>
                </form>
            @endif
            @php($dispatchEligible = app(\App\Services\Production\JobProductionControlService::class)->dispatchEligibility($jobCard)['eligible'])
            @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::ReadyForDispatch) && $dispatchEligible)
                <form method="POST" action="{{ route('admin.production.job-cards.ready-for-dispatch', $jobCard) }}">@csrf
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Ready for dispatch') }}</button>
                </form>
            @endif
        @endcan
        @can('transition', $jobCard)
            @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::OnHold))
                <form method="POST" action="{{ route('admin.production.job-cards.hold', $jobCard) }}">@csrf
                    <button type="submit" class="erp-btn-secondary text-sm">{{ __('On hold') }}</button>
                </form>
            @endif
            @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::Cancelled))
                <form method="POST" action="{{ route('admin.production.job-cards.cancel', $jobCard) }}">@csrf
                    <button type="submit" class="erp-btn-secondary text-sm text-red-600">{{ __('Cancel') }}</button>
                </form>
            @endif
        @endcan
    </div>

    @can('schedule', $jobCard)
        <form method="POST" action="{{ route('admin.production.job-cards.schedule', $jobCard) }}" class="mt-4 flex flex-wrap items-end gap-2">
            @csrf
            <div>
                <label class="block text-xs text-slate-500">{{ __('Planned start') }}</label>
                <input type="date" name="planned_start_date" class="erp-input text-sm" value="{{ $jobCard->planned_start_date?->format('Y-m-d') }}" required>
            </div>
            <div>
                <label class="block text-xs text-slate-500">{{ __('Planned end') }}</label>
                <input type="date" name="planned_end_date" class="erp-input text-sm" value="{{ $jobCard->planned_end_date?->format('Y-m-d') }}" required>
            </div>
            <button type="submit" class="erp-btn-secondary text-sm">{{ __('Update schedule') }}</button>
        </form>
    @endcan
</x-admin.card>
