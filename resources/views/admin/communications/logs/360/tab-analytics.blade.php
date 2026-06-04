<div class="comm-log-360__analytics-grid">
    <div class="comm-log-360__analytics-card comm-log-360__analytics-card--rate">
        <span class="comm-log-360__analytics-label">{{ __('Delivery rate') }}</span>
        <span class="comm-log-360__analytics-value">{{ $deliveryRateLabel }}</span>
        <span class="comm-log-360__analytics-hint">{{ __('Based on current status') }}</span>
    </div>
    <div class="comm-log-360__analytics-card comm-log-360__analytics-card--failures">
        <span class="comm-log-360__analytics-label">{{ __('Failures') }}</span>
        <span class="comm-log-360__analytics-value">{{ $failureCount }}</span>
        <span class="comm-log-360__analytics-hint">{{ __('Delivery events flagged failed') }}</span>
    </div>
    <div class="comm-log-360__analytics-card">
        <span class="comm-log-360__analytics-label">{{ __('Recipients') }}</span>
        <span class="comm-log-360__analytics-value">{{ $recipientCount }}</span>
        <span class="comm-log-360__analytics-hint">{{ __('Total audience') }}</span>
    </div>
    <div class="comm-log-360__analytics-card">
        <span class="comm-log-360__analytics-label">{{ __('Response') }}</span>
        <span class="comm-log-360__analytics-value comm-log-360__analytics-value--sm">{{ $responseLabel }}</span>
        <span class="comm-log-360__analytics-hint">{{ __('Read receipt / engagement') }}</span>
    </div>
</div>

<section class="comm-log-360__card mt-4">
    <h2 class="comm-log-360__card-title">{{ __('Delivery insights') }}</h2>
    <p class="text-sm text-slate-600">
        {{ __('This panel is ready for extended analytics — channel benchmarks, cohort comparisons, and response funnels can plug in here without changing the communication log record.') }}
    </p>
    <dl class="comm-log-360__dl mt-4">
        <div>
            <dt>{{ __('Events logged') }}</dt>
            <dd>{{ $eventCount }}</dd>
        </div>
        <div>
            <dt>{{ __('Channel') }}</dt>
            <dd>{{ $log->channel->label() }}</dd>
        </div>
        <div>
            <dt>{{ __('Final status') }}</dt>
            <dd>{{ $log->status->label() }}</dd>
        </div>
    </dl>
</section>
