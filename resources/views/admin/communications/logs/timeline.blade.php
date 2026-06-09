<x-admin-layout :title="__('Communication Timeline')" :breadcrumbs="[['label' => __('Communication Logs'), 'url' => route('admin.communications.logs.dashboard')], ['label' => __('Timeline')]]">
    @include('admin.communications.logs.partials.nav')

    <x-admin.page-header :title="__('Timeline')" :description="__('Newest first — filter by channel, status, and date.')" />

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()" turbo-frame="erp-main">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search recipient, reference, body…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="channel" class="erp-toolbar-select" aria-label="{{ __('Channel') }}">
                <option value="">{{ __('All channels') }}</option>
                @foreach (\App\Enums\CommunicationLogChannel::cases() as $ch)
                    <option value="{{ $ch->value }}" @selected(($filters['channel'] ?? '') === $ch->value)>{{ $ch->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (\App\Enums\CommunicationLogStatus::cases() as $st)
                    <option value="{{ $st->value }}" @selected(($filters['status'] ?? '') === $st->value)>{{ $st->label() }}</option>
                @endforeach
            </select>
            <select name="branch_id" class="erp-toolbar-select" aria-label="{{ __('Branch') }}">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('From') }}">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-toolbar-input" aria-label="{{ __('To') }}">
            <select name="sort" class="erp-toolbar-select" aria-label="{{ __('Sort') }}">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>{{ __('Newest first') }}</option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>{{ __('Oldest first') }}</option>
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <div class="erp-card">
        <x-admin.communication-timeline :logs="$logs" />
        @if ($logs->hasPages())
            <div class="mt-4 border-t pt-3">{{ $logs->links() }}</div>
        @endif
    </div>
</x-admin-layout>
