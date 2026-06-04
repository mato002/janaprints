<x-admin-layout :title="__('Communication Timeline')" :breadcrumbs="[['label' => __('Communication Logs'), 'url' => route('admin.communications.logs.dashboard')], ['label' => __('Timeline')]]">
    @include('admin.communications.logs.partials.nav')

    <x-admin.page-header :title="__('Timeline')" :description="__('Newest first — filter by channel, status, and date.')" />

    <form method="GET" class="erp-card mb-4" data-turbo-frame="erp-main">
        <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="erp-input" placeholder="{{ __('Search recipient, reference, body…') }}">
            <select name="channel" class="erp-input" onchange="this.form.submit()">
                <option value="">{{ __('All channels') }}</option>
                @foreach (\App\Enums\CommunicationLogChannel::cases() as $ch)
                    <option value="{{ $ch->value }}" @selected(($filters['channel'] ?? '') === $ch->value)>{{ $ch->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="erp-input" onchange="this.form.submit()">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (\App\Enums\CommunicationLogStatus::cases() as $st)
                    <option value="{{ $st->value }}" @selected(($filters['status'] ?? '') === $st->value)>{{ $st->label() }}</option>
                @endforeach
            </select>
            <select name="branch_id" class="erp-input" onchange="this.form.submit()">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}" @selected(($filters['branch_id'] ?? '') == $branch->id)>{{ $branch->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="erp-input">
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="erp-input">
            <select name="sort" class="erp-input" onchange="this.form.submit()">
                <option value="newest" @selected(($filters['sort'] ?? 'newest') === 'newest')>{{ __('Newest first') }}</option>
                <option value="oldest" @selected(($filters['sort'] ?? '') === 'oldest')>{{ __('Oldest first') }}</option>
            </select>
            <button type="submit" class="erp-btn erp-btn--primary erp-btn--sm">{{ __('Apply') }}</button>
        </div>
    </form>

    <div class="erp-card">
        <x-admin.communication-timeline :logs="$logs" />
        @if ($logs->hasPages())
            <div class="mt-4 border-t pt-3">{{ $logs->links() }}</div>
        @endif
    </div>
</x-admin-layout>
