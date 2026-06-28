@if (! ($tabData['can_view'] ?? false))
    <x-admin.empty-state icon="lock-closed" :title="__('Communications access required')" />
@elseif (empty($tabData['communications']))
    <x-admin.card>
        <p class="text-sm text-slate-600">{{ __('No email communications linked to this job yet.') }}</p>
    </x-admin.card>
@else
    <x-admin.card>
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Job communications') }}</h3>
        <x-admin.customer-timeline-feed :events="$tabData['communications']" />
    </x-admin.card>
@endif
