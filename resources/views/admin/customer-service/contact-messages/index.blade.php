<x-admin-layout
    :title="__('Contact Messages')"
    :breadcrumbs="[
        ['label' => __('Commercial'), 'url' => route('admin.workspaces.commercial')],
        ['label' => __('Customer Service'), 'url' => route('admin.workspaces.commercial.section', 'customer-service')],
        ['label' => __('Contact Messages')],
    ]"
>
    <x-admin.page-header
        :title="__('Public Contact Messages')"
        :description="__('Storefront contact form submissions from guest visitors.')"
    />

    <x-admin.kpi-strip>
        <x-admin.kpi-widget :label="__('Unread')" :value="$stats['unread_contact_messages']" icon="inbox" />
        <x-admin.kpi-widget :label="__('New today')" :value="$stats['new_contact_messages']" icon="bell" />
        <x-admin.kpi-widget :label="__('Pending quotes')" :value="$stats['pending_quote_requests']" icon="document-text" />
        <x-admin.kpi-widget :label="__('New quotes today')" :value="$stats['new_quote_requests']" icon="sparkles" />
    </x-admin.kpi-strip>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="route('admin.public-contact-messages.index')" :reset-url="route('admin.public-contact-messages.index')">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="erp-toolbar-input min-w-[12rem] flex-1" placeholder="{{ __('Search name, company, phone, email, subject…') }}" aria-label="{{ __('Search') }}" data-erp-auto-search>
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (App\Enums\PublicContactMessageStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="erp-toolbar-input" value="{{ $filters['date_from'] ?? '' }}" aria-label="{{ __('From date') }}">
            <input type="date" name="date_to" class="erp-toolbar-input" value="{{ $filters['date_to'] ?? '' }}" aria-label="{{ __('To date') }}">
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.data-table
        :searchable="false"
        export-filename="contact-messages"
    >
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('Received') }}</th>
                <th scope="col">{{ __('From') }}</th>
                <th scope="col">{{ __('Subject') }}</th>
                <th scope="col">{{ __('Contact') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($contactMessages as $contactMessage)
                <tr>
                    <td class="whitespace-nowrap">{{ $contactMessage->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <div class="font-medium">{{ $contactMessage->name }}</div>
                        @if ($contactMessage->company)
                            <div class="text-xs text-slate-500">{{ $contactMessage->company }}</div>
                        @endif
                    </td>
                    <td class="max-w-xs truncate">{{ $contactMessage->subject }}</td>
                    <td>
                        <div>{{ $contactMessage->email }}</div>
                        @if ($contactMessage->phone)
                            <div class="text-xs text-slate-500">{{ $contactMessage->phone }}</div>
                        @endif
                    </td>
                    <td>
                        <x-admin.status-badge :variant="$contactMessage->status->badgeVariant()">
                            {{ $contactMessage->status->label() }}
                        </x-admin.status-badge>
                    </td>
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            <x-admin.table-row-action :href="route('admin.public-contact-messages.show', $contactMessage)">{{ __('View') }}</x-admin.table-row-action>
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-admin.empty-state icon="inbox" :title="__('No contact messages yet')" :description="__('Guest contact form submissions from the storefront will appear here.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$contactMessages" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
