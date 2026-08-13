@php
    $rowActions = app(\App\Support\Sales\SalesDeskActionPresenter::class)->rowActions($order);
@endphp
<x-admin.table-row-actions>
    @foreach ($rowActions as $action)
        @if (! empty($action['action']))
            <x-admin.table-row-action
                :action="$action['action']"
                :method="$action['method'] ?? 'POST'"
                :confirm="$action['confirm'] ?? null"
                :variant="$action['variant'] ?? 'default'"
            >{{ $action['label'] }}</x-admin.table-row-action>
        @elseif (! empty($action['new_tab']))
            <x-admin.table-row-action
                :href="$action['href']"
                :variant="$action['variant'] ?? 'default'"
                target="_blank"
                rel="noopener"
                data-turbo="false"
                data-no-modal
            >{{ $action['label'] }}</x-admin.table-row-action>
        @elseif (! empty($action['modal']))
            <x-admin.table-row-action
                :href="$action['href']"
                :variant="$action['variant'] ?? 'default'"
                data-erp-modal-open
            >{{ $action['label'] }}</x-admin.table-row-action>
        @else
            <x-admin.table-row-action
                :href="$action['href']"
                :variant="$action['variant'] ?? 'default'"
            >{{ $action['label'] }}</x-admin.table-row-action>
        @endif
    @endforeach
</x-admin.table-row-actions>
