<section class="comm-log-360__kpi-strip" aria-label="{{ __('Communication KPIs') }}">
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label">{{ __('Message status') }}</span>
        <span class="comm-log-360__kpi-value">{{ $log->status->label() }}</span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label">{{ __('Recipients') }}</span>
        <span class="comm-log-360__kpi-value">{{ $recipientCount }}</span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label">{{ __('Delivery events') }}</span>
        <span class="comm-log-360__kpi-value">{{ $eventCount }}</span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label">{{ __('Channel') }}</span>
        <span class="comm-log-360__kpi-value comm-log-360__kpi-value--sm">{{ $log->channel->label() }}</span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label">{{ __('Created by') }}</span>
        <span class="comm-log-360__kpi-value comm-log-360__kpi-value--sm">{{ $log->creator?->name ?? '—' }}</span>
    </div>
    <div class="comm-log-360__kpi">
        <span class="comm-log-360__kpi-label">{{ __('Sent time') }}</span>
        <span class="comm-log-360__kpi-value comm-log-360__kpi-value--sm">{{ $log->sent_at?->format('d M H:i') ?? '—' }}</span>
    </div>
</section>
