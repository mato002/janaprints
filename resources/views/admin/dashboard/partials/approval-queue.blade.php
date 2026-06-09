@php
    $approvals = $dashboard['approvals'] ?? null;
@endphp

@if (! empty($approvals['visible']))
    <section class="exec-panel exec-panel--approvals" aria-label="{{ __('Executive approval queue') }}">
        <div class="exec-panel__head">
            <h2 class="exec-panel__title">{{ __('Executive Approval Queue') }}</h2>
            @if (! empty($approvals['queue_url']))
                <a href="{{ $approvals['queue_url'] }}" data-turbo-frame="erp-main" class="exec-panel__meta exec-panel__meta--link">
                    {{ __('View full queue') }}
                </a>
            @endif
        </div>

        <div class="exec-approval-summary mb-3 grid grid-cols-3 gap-2">
            <div class="exec-approval-summary__item">
                <span class="exec-approval-summary__label">{{ __('Waiting') }}</span>
                <span class="exec-approval-summary__value">{{ $approvals['summary']['waiting'] }}</span>
            </div>
            <div class="exec-approval-summary__item exec-approval-summary__item--critical">
                <span class="exec-approval-summary__label">{{ __('Critical') }}</span>
                <span class="exec-approval-summary__value">{{ $approvals['summary']['critical'] }}</span>
            </div>
            <div class="exec-approval-summary__item exec-approval-summary__item--aging">
                <span class="exec-approval-summary__label">{{ __('Aging') }}</span>
                <span class="exec-approval-summary__value">{{ $approvals['summary']['aging'] }}</span>
            </div>
        </div>

        @include('admin.executive.approvals.partials.table', [
            'rows' => collect($approvals['items'])->take(8),
            'canAction' => $approvals['can_action'],
        ])
    </section>
@endif
