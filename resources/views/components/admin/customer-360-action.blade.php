@props(['customer'])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-2']) }}>
    <x-admin.crm-btn
        variant="outline"
        size="sm"
        :href="route('admin.crm.customers.show', $customer)"
        data-turbo-frame="erp-main"
    >{{ __('Customer 360') }}</x-admin.crm-btn>
    @can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)
        <form method="POST" action="{{ route('admin.communications.inbox.customers.start', $customer) }}" class="inline" data-turbo-frame="erp-main">
            @csrf
            <x-admin.crm-btn type="submit" variant="primary" size="sm">{{ __('Start conversation') }}</x-admin.crm-btn>
        </form>
    @endcan
</div>
