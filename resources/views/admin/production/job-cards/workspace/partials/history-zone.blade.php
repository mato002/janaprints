<section class="job-360-zone job-360-zone--history" aria-label="{{ __('History') }}">
    <header class="job-360-zone__head">
        <x-admin.icon name="clock" class="h-5 w-5 text-slate-500" />
        <h2 class="job-360-zone__title">{{ __('History') }}</h2>
    </header>

    <div class="job-360-history-links">
        <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'timeline']) }}" class="job-360-history-links__item" data-turbo-frame="erp-main">
            <x-admin.icon name="switch-horizontal" class="h-4 w-4" />
            <span>{{ __('Timeline') }}</span>
        </a>
        <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'communications']) }}" class="job-360-history-links__item" data-turbo-frame="erp-main">
            <x-admin.icon name="document-text" class="h-4 w-4" />
            <span>{{ __('Communications') }}</span>
        </a>
        <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'traceability']) }}" class="job-360-history-links__item" data-turbo-frame="erp-main">
            <x-admin.icon name="clipboard-list" class="h-4 w-4" />
            <span>{{ __('Traceability') }}</span>
        </a>
        <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'artwork']) }}" class="job-360-history-links__item" data-turbo-frame="erp-main">
            <x-admin.icon name="photograph" class="h-4 w-4" />
            <span>{{ __('Attachments') }}</span>
        </a>
    </div>
</section>
