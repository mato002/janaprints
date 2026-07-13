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

    <x-admin.kpi-strip>
        <x-admin.kpi-widget :label="__('New today')" :value="$stats['new_quote_requests']" icon="sparkles" :hint="__('Pending requests received today')" />
        <x-admin.kpi-widget :label="__('Pending')" :value="$stats['pending_quote_requests']" icon="clock" />
        <x-admin.kpi-widget :label="__('Unread messages')" :value="$stats['unread_contact_messages']" icon="inbox" />
        <x-admin.kpi-widget :label="__('New messages today')" :value="$stats['new_contact_messages']" icon="inbox" />
    </x-admin.kpi-strip>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.public-quote-requests.index')" :reset-url="route('admin.public-quote-requests.index')">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search name, company, phone, email…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (App\Enums\PublicQuoteRequestStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <select name="service_needed" class="erp-toolbar-select" aria-label="{{ __('Service') }}">
                <option value="">{{ __('All services') }}</option>
                @foreach ($services as $service)
                    <option value="{{ $service }}" @selected(($filters['service_needed'] ?? '') === $service)>{{ $service }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="erp-toolbar-input" value="{{ $filters['date_from'] ?? '' }}" aria-label="{{ __('From date') }}">
            <input type="date" name="date_to" class="erp-toolbar-input" value="{{ $filters['date_to'] ?? '' }}" aria-label="{{ __('To date') }}">
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :searchable="false"
        export-filename="quote-requests"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Received') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col">{{ __('Service') }}</th>
                <th scope="col">{{ __('Contact') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
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
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.public-quote-requests.show', $quoteRequest)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state icon="document-text" :title="__('No quote requests yet')" :description="__('Guest quote submissions from the storefront will appear here.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$quoteRequests" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
