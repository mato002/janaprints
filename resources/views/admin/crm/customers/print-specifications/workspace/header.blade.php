@php
    $header = $workspace['header'];
@endphp

<header class="flex flex-col gap-3 border-b border-erp-border pb-4 sm:flex-row sm:items-start sm:justify-between">
    <div class="min-w-0">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">{{ $header['code'] }}</p>
        <h1 class="truncate text-lg font-semibold text-slate-900">{{ $header['name'] }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ $header['product_name'] ?? __('No product linked') }}
            @if ($header['artwork_version'])
                · {{ __('Artwork') }} {{ $header['artwork_version'] }}
            @endif
        </p>
    </div>

    <div class="flex items-center gap-2">
        <span class="erp-badge">{{ $header['status'] }}</span>

        <x-admin.table-row-actions align="left">
            <x-admin.table-row-action :href="route('admin.crm.customers.print-specifications.show', [$customer, $specification])">
                {{ __('Open') }}
            </x-admin.table-row-action>
            @can('update', $customer)
                @unless ($header['is_read_only'])
                    <x-admin.table-row-action
                        :href="route('admin.crm.customers.print-specifications.edit', [$customer, $specification])"
                        data-turbo-frame="erp-form-modal"
                    >
                        {{ __('Edit') }}
                    </x-admin.table-row-action>
                @endunless
            @endcan
            @can('sales_orders.create')
                @if ($specification->isSelectableForOrders())
                    <x-admin.table-row-action
                        :href="route('admin.sales-orders.create', ['customer_id' => $customer->id, 'tab' => 'direct', 'print_specification_id' => $specification->id])"
                        data-turbo-frame="erp-form-modal"
                    >
                        {{ __('Create Order') }}
                    </x-admin.table-row-action>
                @endif
            @endcan
            @if (! empty($workspace['allowed_transitions']) && auth()->user()?->can('update', $customer))
                @foreach ($workspace['allowed_transitions'] as $transition)
                    <form method="POST" action="{{ route('admin.crm.customers.print-specifications.transition', [$customer, $specification]) }}" class="block">
                        @csrf
                        <input type="hidden" name="status" value="{{ $transition->value }}">
                        <button
                            type="submit"
                            class="flex w-full items-center gap-2 px-3 py-2 text-left text-sm text-erp-primary hover:bg-erp-page"
                            @click="$dispatch('erp-row-menu-close')"
                        >
                            {{ __('Mark :status', ['status' => $transition->label()]) }}
                        </button>
                    </form>
                @endforeach
            @endif
        </x-admin.table-row-actions>
    </div>
</header>
