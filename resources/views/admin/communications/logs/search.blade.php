<x-admin-layout :title="__('Search communications')" :breadcrumbs="[['label' => __('Communication Logs'), 'url' => route('admin.communications.logs.dashboard')], ['label' => __('Search')]]">
    @include('admin.communications.logs.partials.nav')
    <x-admin.page-header :title="__('Global communication search')" />

    <form method="GET" class="erp-card mb-4 flex gap-2" data-turbo-frame="erp-main">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="erp-input flex-1" placeholder="{{ __('Phone, email, template, reference, recipient…') }}" autofocus>
        <button type="submit" class="erp-btn erp-btn--primary">{{ __('Search') }}</button>
    </form>

    <div class="erp-card">
        <x-admin.communication-timeline :logs="$logs" />
        @if ($logs->hasPages())<div class="mt-4">{{ $logs->links() }}</div>@endif
    </div>
</x-admin-layout>
