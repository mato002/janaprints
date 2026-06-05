@php
    $activeOnPage = $customers->filter(fn ($c) => $c->status->value === 'active')->count();
    $inactiveOnPage = $customers->filter(fn ($c) => $c->status->value === 'inactive')->count();
@endphp

<x-admin-layout :title="__('Customers')" :breadcrumbs="[['label' => __('CRM')], ['label' => __('Customers')]]">
    <div class="crm-customers w-full min-w-0 space-y-4">
        <header class="crm-customers__header">
            <div class="crm-customers__header-main">
                <div>
                    <h1 class="crm-customers__title">{{ __('Customers') }}</h1>
                    <p class="crm-customers__subtitle">{{ __('Customer accounts, contacts, and communication history.') }}</p>
                </div>
                @can('create', App\Models\Crm\Customer::class)
                    <x-admin.crm-btn
                        variant="primary"
                        :href="route('admin.crm.customers.create')"
                        class="shrink-0"
                        data-turbo-frame="erp-main"
                    >{{ __('Create customer') }}</x-admin.crm-btn>
                @endcan
            </div>
        </header>

        <section class="crm-customers__kpi-strip" aria-label="{{ __('Customer list summary') }}">
            <div class="crm-customers__kpi">
                <span class="crm-customers__kpi-label">{{ __('Total customers') }}</span>
                <span class="crm-customers__kpi-value">{{ number_format($customers->total()) }}</span>
            </div>
            <div class="crm-customers__kpi">
                <span class="crm-customers__kpi-label">{{ __('On this page') }}</span>
                <span class="crm-customers__kpi-value">{{ $customers->count() }}</span>
            </div>
            <div class="crm-customers__kpi">
                <span class="crm-customers__kpi-label">{{ __('Active (page)') }}</span>
                <span class="crm-customers__kpi-value">{{ $activeOnPage }}</span>
            </div>
            <div class="crm-customers__kpi">
                <span class="crm-customers__kpi-label">{{ __('Inactive (page)') }}</span>
                <span class="crm-customers__kpi-value">{{ $inactiveOnPage }}</span>
            </div>
        </section>

        <p class="crm-customers__hint">
            <span aria-hidden="true">↗</span>
            {{ __('Click any row to open the customer 360 workspace.') }}
        </p>

        <x-admin.data-table
            class="crm-customers__data-grid"
            :search-placeholder="__('Search customers…')"
            export-filename="customers"
            :chips="[
                ['id' => 'all', 'label' => __('All')],
                ['id' => 'active', 'label' => __('Active')],
                ['id' => 'inactive', 'label' => __('Inactive')],
            ]"
        >
            <x-slot name="head">
                <tr>
                    <th scope="col">{{ __('Customer') }}</th>
                    <th scope="col" class="hidden md:table-cell">{{ __('Branch') }}</th>
                    <th scope="col" class="hidden lg:table-cell">{{ __('Contact') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col" class="erp-table-actions-col"><span class="sr-only">{{ __('Actions') }}</span></th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @forelse ($customers as $customer)
                    @php
                        $search = strtolower($customer->customer_code.' '.$customer->company_name.' '.($customer->branch?->name ?? '').' '.$customer->status->value.' '.($customer->phone ?? '').' '.($customer->email ?? ''));
                        $chip = strtolower($customer->status->value);
                        $initial = mb_strtoupper(mb_substr($customer->company_name, 0, 1));
                        $showUrl = route('admin.crm.customers.show', $customer);
                        $statusClass = match ($customer->status->value) {
                            'active' => 'crm-customers__status--active',
                            'inactive' => 'crm-customers__status--inactive',
                            default => 'crm-customers__status--prospect',
                        };
                    @endphp
                    <tr
                        x-show="rowVisible(@js($search), @js($chip))"
                        class="crm-customers__row group"
                        data-href="{{ $showUrl }}"
                        data-turbo-frame="erp-main"
                        role="link"
                        tabindex="0"
                        aria-label="{{ __('Open :name', ['name' => $customer->company_name]) }}"
                        @click="if (!$event.target.closest('[data-erp-row-actions]')) { const url = $el.dataset.href; if (window.Turbo) { window.Turbo.visit(url, { frame: $el.dataset.turboFrame || 'erp-main' }); } else { window.location.href = url; } }"
                        @keydown.enter.prevent="if (!$event.target.closest('[data-erp-row-actions]')) { $el.click(); }"
                    >
                        <td class="crm-customers__cell-customer">
                            <div class="flex items-center gap-3">
                                <div class="crm-customers__avatar" aria-hidden="true">{{ $initial }}</div>
                                <div class="min-w-0">
                                    <div class="truncate font-semibold text-slate-900 group-hover:text-indigo-800">{{ $customer->company_name }}</div>
                                    <div class="font-mono text-[11px] text-slate-500">{{ $customer->customer_code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="hidden md:table-cell text-slate-600">{{ $customer->branch?->name ?? '—' }}</td>
                        <td class="hidden lg:table-cell">
                            <div class="min-w-0 text-sm text-slate-600">
                                @if ($customer->phone)
                                    <p class="truncate">{{ $customer->phone }}</p>
                                @endif
                                @if ($customer->email)
                                    <p class="truncate text-xs text-slate-500">{{ $customer->email }}</p>
                                @endif
                                @if (! $customer->phone && ! $customer->email)
                                    <span class="text-slate-400">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="crm-customers__status {{ $statusClass }}">{{ ucfirst($customer->status->value) }}</span>
                        </td>
                        <td class="erp-table-actions-col" @click.stop>
                            <x-admin.table-row-actions>
                                <x-admin.table-row-action :href="$showUrl" data-turbo-frame="erp-main">{{ __('View 360') }}</x-admin.table-row-action>
                                @can('viewAny', App\Models\Communications\Inbox\CommunicationConversation::class)
                                    <x-admin.table-row-action
                                        :action="route('admin.communications.inbox.customers.start', $customer)"
                                        method="POST"
                                    >{{ __('Start conversation') }}</x-admin.table-row-action>
                                @endcan
                                @can('update', $customer)
                                    <x-admin.table-row-action :href="route('admin.crm.customers.edit', $customer)" data-turbo-frame="erp-main">{{ __('Edit') }}</x-admin.table-row-action>
                                @endcan
                                @can('deactivate', $customer)
                                    <x-admin.table-row-action
                                        :action="route('admin.crm.customers.deactivate', $customer)"
                                        method="POST"
                                    >{{ __('Deactivate') }}</x-admin.table-row-action>
                                @endcan
                                @can('delete', $customer)
                                    <x-admin.table-row-action
                                        :action="route('admin.crm.customers.destroy', $customer)"
                                        method="DELETE"
                                        :confirm="__('Delete this customer permanently?')"
                                    >{{ __('Remove') }}</x-admin.table-row-action>
                                @endcan
                            </x-admin.table-row-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <x-admin.empty-state icon="user-circle" :title="__('No customers yet')" :description="__('Start by adding your first customer account.')">
                                <x-slot name="action">
                                    @can('create', App\Models\Crm\Customer::class)
                                        <x-admin.crm-btn variant="primary" :href="route('admin.crm.customers.create')" data-turbo-frame="erp-main">{{ __('Create customer') }}</x-admin.crm-btn>
                                    @endcan
                                </x-slot>
                            </x-admin.empty-state>
                        </td>
                    </tr>
                @endforelse
            </x-slot>
            <x-slot name="footer"><x-admin.table-pagination :paginator="$customers" /></x-slot>
        </x-admin.data-table>
    </div>
</x-admin-layout>
