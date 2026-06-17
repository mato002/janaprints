<section class="ess-card">
    <h2 class="ess-section-title">{{ __('Onboarding progress') }}</h2>
    <ol class="ess-timeline mt-4 space-y-4">
        @foreach ($onboarding['steps'] as $step)
            <li @class(['ess-timeline__item', 'ess-timeline__item--done' => $step['done']])>
                <span class="ess-timeline__dot" aria-hidden="true"></span>
                <div>
                    <p class="font-medium">{{ $step['label'] }}</p>
                    <p class="text-sm text-erp-muted">{{ $step['done'] ? __('Completed') : __('Pending') }}</p>
                </div>
            </li>
        @endforeach
    </ol>
</section>
