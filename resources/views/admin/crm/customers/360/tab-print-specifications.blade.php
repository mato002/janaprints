<div class="space-y-4">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-sm font-semibold text-slate-900">{{ __('Print Specifications') }}</h3>
        @can('update', $customer)
            <x-admin.crm-btn
                variant="primary"
                size="sm"
                :href="route('admin.crm.customers.print-specifications.create', $customer)"
                data-turbo-frame="erp-form-modal"
                class="min-h-[2.75rem]"
            >{{ __('New specification') }}</x-admin.crm-btn>
        @endcan
    </div>

    <form method="GET" action="{{ route('admin.crm.customers.show', $customer) }}" class="grid grid-cols-1 gap-2 rounded-lg border border-erp-border p-3 sm:grid-cols-2 lg:grid-cols-6">
        <input type="hidden" name="tab" value="print-specifications">
        <div class="lg:col-span-2">
            <label class="erp-label" for="spec_search">{{ __('Search') }}</label>
            <input id="spec_search" name="spec_search" class="erp-input w-full" value="{{ request('spec_search') }}" placeholder="{{ __('Code, name…') }}">
        </div>
        <div>
            <label class="erp-label" for="spec_status">{{ __('Status') }}</label>
            <select id="spec_status" name="spec_status" class="erp-input w-full">
                <option value="">{{ __('All') }}</option>
                @foreach ($printSpecStatuses as $status)
                    <option value="{{ $status->value }}" @selected(request('spec_status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="erp-label" for="spec_serial_prefix">{{ __('Serial prefix') }}</label>
            <input id="spec_serial_prefix" name="spec_serial_prefix" class="erp-input w-full" value="{{ request('spec_serial_prefix') }}">
        </div>
        <div>
            <label class="erp-label" for="spec_artwork_version">{{ __('Artwork version') }}</label>
            <input id="spec_artwork_version" name="spec_artwork_version" type="number" min="1" class="erp-input w-full" value="{{ request('spec_artwork_version') }}">
        </div>
        <div class="flex items-end gap-2">
            <x-admin.crm-btn variant="outline" size="sm" type="submit" class="min-h-[2.5rem]">{{ __('Filter') }}</x-admin.crm-btn>
            <x-admin.crm-btn variant="ghost" size="sm" :href="route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications'])" class="min-h-[2.5rem]">{{ __('Reset') }}</x-admin.crm-btn>
        </div>
    </form>

    @if ($printSpecifications->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Code') }}</th>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Product') }}</th>
                        <th>{{ __('Artwork') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th class="text-right">{{ __('Orders') }}</th>
                        <th class="text-right">{{ __('Revenue') }}</th>
                        <th>{{ __('Last ordered') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($printSpecifications as $spec)
                        @php
                            $activeArt = $spec->activeArtworkVersion;
                        @endphp
                        <tr>
                            <td><code class="text-xs">{{ $spec->specification_code }}</code></td>
                            <td>
                                <a class="font-medium text-erp-accent hover:underline" href="{{ route('admin.crm.customers.print-specifications.show', [$customer, $spec]) }}">
                                    {{ $spec->name }}
                                </a>
                            </td>
                            <td>{{ $spec->productLabel() }}</td>
                            <td>{{ $activeArt?->versionLabel() ?? '—' }}</td>
                            <td><span class="erp-badge">{{ $spec->status->label() }}</span></td>
                            <td class="text-right tabular-nums">{{ $spec->orders_count ?? 0 }}</td>
                            <td class="text-right tabular-nums">{{ number_format((float) ($spec->total_revenue ?? 0), 2) }}</td>
                            <td>{{ $spec->last_used_at ? \Illuminate\Support\Carbon::parse($spec->last_used_at)->format('Y-m-d') : '—' }}</td>
                            <td class="erp-table-actions-col">
                                <x-admin.table-row-actions>
                                    <x-admin.table-row-action :href="route('admin.crm.customers.print-specifications.show', [$customer, $spec])">
                                        {{ __('Open') }}
                                    </x-admin.table-row-action>
                                    @can('update', $customer)
                                        @unless ($spec->isReadOnly())
                                            <x-admin.table-row-action
                                                :href="route('admin.crm.customers.print-specifications.edit', [$customer, $spec])"
                                                data-turbo-frame="erp-form-modal"
                                            >
                                                {{ __('Edit') }}
                                            </x-admin.table-row-action>
                                        @endunless
                                    @endcan
                                    @can('sales_orders.create')
                                        @if ($spec->isSelectableForOrders())
                                            <x-admin.table-row-action
                                                :href="route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'direct', 'print_specification_id' => $spec->id])"
                                                data-turbo-frame="erp-form-modal"
                                            >
                                                {{ __('Create Order') }}
                                            </x-admin.table-row-action>
                                        @endif
                                    @endcan
                                </x-admin.table-row-actions>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $printSpecifications->withQueryString()->links() }}</div>
    @else
        <x-admin.empty-state
            icon="document-text"
            :title="__('No print specifications yet')"
            :description="__('Create a specification for a finished product — for example a book, brochure, or flyer.')"
        >
            @can('update', $customer)
                <x-slot:action>
                    <x-admin.crm-btn
                        variant="primary"
                        size="sm"
                        :href="route('admin.crm.customers.print-specifications.create', $customer)"
                        data-turbo-frame="erp-form-modal"
                        class="min-h-[2.75rem]"
                    >{{ __('New specification') }}</x-admin.crm-btn>
                </x-slot:action>
            @endcan
        </x-admin.empty-state>
    @endif
</div>
