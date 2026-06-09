<div class="crm-360__tab-stack">
    @can('update', $lead)
        <section class="crm-360__card crm-360__card--form">
            <h2 class="crm-360__card-title">{{ __('Schedule follow-up') }}</h2>
            <form method="POST" action="{{ route('admin.crm.leads.follow-ups.store', $lead) }}" class="crm-360__form-grid">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Scheduled at') }}</label>
                    <x-text-input name="scheduled_at" type="datetime-local" class="w-full" required />
                </div>
                <div class="sm:col-span-2">
                    <label class="erp-label">{{ __('Notes') }}</label>
                    <textarea name="notes" class="erp-input w-full text-sm" rows="2"></textarea>
                </div>
                <div class="sm:col-span-3">
                    <x-admin.crm-btn type="submit" variant="primary" size="sm">{{ __('Schedule follow-up') }}</x-admin.crm-btn>
                </div>
            </form>
        </section>
    @endcan

    <section class="crm-360__card">
        <h2 class="crm-360__card-title text-red-600">{{ __('Overdue') }} ({{ $followUps['overdue']->count() }})</h2>
        @include('admin.crm.leads.360.partials.follow-up-list', ['items' => $followUps['overdue'], 'empty' => __('No overdue follow-ups')])
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Scheduled') }} ({{ $followUps['scheduled']->count() }})</h2>
        @include('admin.crm.leads.360.partials.follow-up-list', ['items' => $followUps['scheduled'], 'empty' => __('No scheduled follow-ups')])
    </section>

    <section class="crm-360__card">
        <h2 class="crm-360__card-title">{{ __('Completed') }} ({{ $followUps['completed']->count() }})</h2>
        @include('admin.crm.leads.360.partials.follow-up-list', ['items' => $followUps['completed'], 'empty' => __('No completed follow-ups'), 'showComplete' => false])
    </section>
</div>
