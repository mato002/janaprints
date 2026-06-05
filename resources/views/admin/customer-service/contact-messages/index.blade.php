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

    <div class="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-admin.kpi-widget :label="__('Unread')" :value="$stats['unread_contact_messages']" icon="inbox" />
        <x-admin.kpi-widget :label="__('New today')" :value="$stats['new_contact_messages']" icon="mail" />
        <x-admin.kpi-widget :label="__('Pending quotes')" :value="$stats['pending_quote_requests']" icon="document-text" />
        <x-admin.kpi-widget :label="__('New quotes today')" :value="$stats['new_quote_requests']" icon="sparkles" />
    </div>

    <x-admin.card class="mb-4">
        <form method="GET" action="{{ route('admin.public-contact-messages.index') }}" class="grid grid-cols-1 gap-3 p-4 md:grid-cols-3 lg:grid-cols-5">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="erp-input text-sm lg:col-span-2" placeholder="{{ __('Search name, company, phone, email, subject…') }}">
            <select name="status" class="erp-input text-sm">
                <option value="">{{ __('All statuses') }}</option>
                @foreach (App\Enums\PublicContactMessageStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" class="erp-input text-sm" value="{{ $filters['date_from'] ?? '' }}">
            <input type="date" name="date_to" class="erp-input text-sm" value="{{ $filters['date_to'] ?? '' }}">
            <div class="flex gap-2 lg:col-span-5">
                <button type="submit" class="erp-btn-secondary">{{ __('Filter') }}</button>
                <a href="{{ route('admin.public-contact-messages.index') }}" class="erp-btn-secondary">{{ __('Reset') }}</a>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Received') }}</th>
                        <th>{{ __('From') }}</th>
                        <th>{{ __('Subject') }}</th>
                        <th>{{ __('Contact') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
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
                                <a href="{{ route('admin.public-contact-messages.show', $contactMessage) }}" class="erp-btn-secondary text-xs">{{ __('View') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                <p class="font-medium">{{ __('No contact messages yet') }}</p>
                                <p class="mt-1 text-xs">{{ __('Guest contact form submissions from the storefront will appear here.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($contactMessages->hasPages())
            <div class="border-t border-slate-100 p-4">
                {{ $contactMessages->links() }}
            </div>
        @endif
    </x-admin.card>
</x-admin-layout>
