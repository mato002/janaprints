<x-layouts.client :title="__('Production jobs')" :heading="__('Production jobs')">
    <div class="client-grid client-grid--single">
        @forelse ($jobs as $jobCard)
            <article class="client-card">
                <div class="client-card__head">
                    <div>
                        <p class="client-card__eyebrow">{{ __('Job') }}</p>
                        <h2 class="client-card__title">{{ $jobCard->job_card_number }}</h2>
                    </div>
                    @if (! empty($jobCard->tracking_summary))
                        @include('client.partials.status-badge', ['label' => $jobCard->tracking_summary['status_label']])
                    @endif
                </div>
                <dl class="client-card__meta">
                    @if ($jobCard->salesOrder)
                        <div><dt>{{ __('Order') }}</dt><dd>{{ $jobCard->salesOrder->order_number }}</dd></div>
                    @endif
                    <div><dt>{{ __('Due date') }}</dt><dd>{{ $jobCard->planned_end_date?->format('M j, Y') ?: '—' }}</dd></div>
                </dl>
                <a href="{{ route('client.jobs.show', $jobCard) }}" class="client-btn client-btn--secondary">{{ __('Track job') }}</a>
            </article>
        @empty
            @include('client.partials.empty-state', [
                'icon' => 'clipboard',
                'message' => __('No production jobs yet. Jobs appear here once your orders enter production.'),
            ])
        @endforelse
    </div>
    {{ $jobs->links() }}
</x-layouts.client>
