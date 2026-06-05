<x-admin-layout
    :title="__('Quote Requests')"
    :breadcrumbs="[
        ['label' => __('Commercial'), 'url' => route('admin.workspaces.commercial')],
        ['label' => __('Customer Service'), 'url' => route('admin.workspaces.commercial.section', 'customer-service')],
        ['label' => __('Quote Requests')],
    ]"
>
    <x-admin.page-header
        :title="__('Public Quote Requests')"
        :description="__('Storefront quote requests from guest visitors.')"
    />

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-admin.kpi-widget :label="__('New today')" :value="$stats['new_quote_requests']" icon="sparkles" :hint="__('Pending requests received today')" />
        <x-admin.kpi-widget :label="__('Pending')" :value="$stats['pending_quote_requests']" icon="clock" />
        <x-admin.kpi-widget :label="__('Unread messages')" :value="$stats['unread_contact_messages']" icon="inbox" />
        <x-admin.kpi-widget :label="__('New messages today')" :value="$stats['new_contact_messages']" icon="mail" />
    </div>

    <x-admin.card class="mb-4">
        <form method="GET" action="{{ route('admin.public-quote-requests.index') }}" class="grid grid-cols-1 gap-3 p-4 md:grid-cols-3 lg:grid-cols-6">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="erp-input text-sm lg:col-span-2" placeholder="{{ __('Search name, company, phone, email…') }}">
            <select name="status" class="erp-input text-sm">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (App\Enums\PublicQuoteRequestStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <select name="service_needed" class="erp-input text-sm">
                <option value="">{{ __('All services') }}</option>
                @foreach ($services as $service)
                    <option value="{{ $service }}" @selected(($filters['service_needed'] ?? '') === $service)>{{ $service }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="erp-input text-sm" value="{{ $filters['date_from'] ?? '' }}">
            <input type="date" name="date_to" class="erp-input text-sm" value="{{ $filters['date_to'] ?? '' }}">
            <div class="flex gap-2 lg:col-span-6">
                <button type="submit" class="erp-btn-secondary">{{ __('Filter') }}</button>
                <a href="{{ route('admin.public-quote-requests.index') }}" class="erp-btn-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Received') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Service') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quoteRequests as $quoteRequest)
                        <tr>
                            <td class="whitespace-nowrap">{{ $quoteRequest->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="text-xs font-mono text-slate-500">{{ $quoteRequest->reference() }}</div>
                                <div class="font-medium">{{ $quoteRequest->name }}</div>
                                @if ($quoteRequest->company)
                                    <div class="text-xs text-slate-500">{{ $quoteRequest->company }}</div>
                                @endif
                            </td>
                            <td>{{ $quoteRequest->service_needed }}</td>
                            <td>
                                <div>{{ $quoteRequest->email }}</div>
                                <div class="text-xs text-slate-500">{{ $quoteRequest->phone }}</div>
                            </td>
                            <td>
                                <x-admin.status-badge :variant="$quoteRequest->status->badgeVariant()">
                                    {{ $quoteRequest->status->label() }}
                                </x-admin.status-badge>
                            </td>
                            <td class="erp-table-actions-col">
                                <a href="{{ route('admin.public-quote-requests.show', $quoteRequest) }}" class="erp-btn-secondary text-xs">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <p class="font-medium">{{ __('No quote requests yet') }}</p>
                                <p class="mt-1 text-xs">{{ __('Guest quote submissions from the storefront will appear here.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($quoteRequests->hasPages())
            <div class="border-t border-slate-100 p-4">
                {{ $quoteRequests->links() }}
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>
