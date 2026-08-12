<x-admin-layout :title="__('Email customers')" :breadcrumbs="[['label' => __('Email'), 'url' => route('admin.communications.email.dashboard')], ['label' => __('Customers')]]">
    @include('admin.communications.email.partials.mailbox-chrome', ['activeFolder' => 'customers'])

    <div class="mb-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ __('Customers you email') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Recent recipients this month — open Customer 360 or compose a follow-up.') }}</p>
    </div>

    <div class="erp-card divide-y overflow-hidden">
        @forelse ($customers as $customer)
            <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                <div class="min-w-0">
                    @if ($customer['url'])
                        <a href="{{ $customer['url'] }}" data-turbo-frame="erp-main" class="font-semibold text-erp-primary hover:underline">{{ $customer['customer_name'] }}</a>
                    @else
                        <p class="font-semibold text-erp-primary">{{ $customer['customer_name'] }}</p>
                    @endif
                    <p class="text-sm text-slate-500">{{ trans_choice(':count email|:count emails', $customer['email_count'], ['count' => $customer['email_count']]) }} {{ __('this month') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @if ($customer['url'])
                        <a href="{{ $customer['url'] }}" data-turbo-frame="erp-main" class="erp-btn erp-btn--secondary text-sm">{{ __('Customer 360') }}</a>
                    @endif
                    @can('create', App\Models\Communications\EmailCampaign::class)
                        <a
                            href="{{ route('admin.communications.email.compose', array_filter(['to' => $customer['email'] ?? null, 'customer_id' => $customer['customer_id']])) }}"
                            data-turbo-frame="erp-main"
                            class="erp-btn erp-btn--primary text-sm"
                        >{{ __('Compose') }}</a>
                    @endcan
                </div>
            </div>
        @empty
            <div class="px-4 py-10 text-center text-sm text-slate-500">{{ __('No customer emails this month yet.') }}</div>
        @endforelse
    </div>
</x-admin-layout>
