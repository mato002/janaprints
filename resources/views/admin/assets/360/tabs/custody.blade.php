<x-admin.card>
    <p class="mb-3 text-sm"><strong>{{ __('Current Custodian') }}:</strong> {{ $tabData['current_custodian'] ?? __('Unassigned') }}</p>
    <h3 class="mb-3 text-sm font-semibold">{{ __('Accountability Timeline') }}</h3>
    @include('admin.assets.360.partials.timeline', ['entries' => $tabData['timeline']])
</x-admin.card>
