@php
    $breadcrumbs = [
        ['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')],
        ['label' => __('Inventory Control'), 'url' => route('admin.workspaces.supply-chain.section', ['section' => 'inventory-control'])],
        ['label' => __('Stock Count'), 'url' => route('admin.inventory.stock-counts.index')],
        ['label' => $count->count_number],
    ];
@endphp
<x-admin-layout :title="$count->count_number" :breadcrumbs="$breadcrumbs">
    <x-admin.page-header :title="$count->count_number">
        <x-admin.enum-status-badge :status="$count->status->value" />
        @can('update', $count)
            <a href="{{ route('admin.inventory.stock-counts.worksheet', $count) }}" class="erp-btn-secondary">{{ __('Worksheet') }}</a>
        @endcan
        <x-admin.export-dropdown
            export-route="admin.inventory.stock-counts.export"
            :export-route-params="['stockCount' => $count]"
            :format-in-path="true"
        />
        @can('submit', $count)
            <form method="POST" action="{{ route('admin.inventory.stock-counts.submit', $count) }}">@csrf<button class="erp-btn-primary">{{ __('Submit') }}</button></form>
        @endcan
        @can('approve', $count)
            <form method="POST" action="{{ route('admin.inventory.stock-counts.approve', $count) }}">@csrf<button class="erp-btn-primary">{{ __('Approve') }}</button></form>
        @endcan
        @can('post', $count)
            <form method="POST" action="{{ route('admin.inventory.stock-counts.post', $count) }}">@csrf<button class="erp-btn-primary">{{ __('Post Variance') }}</button></form>
        @endcan
    </x-admin.page-header>

    <x-admin.card>
        <dl class="grid grid-cols-2 gap-4 text-sm mb-4">
            <div><dt class="text-slate-500">{{ __('Warehouse') }}</dt><dd>{{ $count->warehouse?->name }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Count date') }}</dt><dd>{{ $count->count_date->format('Y-m-d') }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ ucfirst($count->count_type->value) }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Created by') }}</dt><dd>{{ $count->creator?->name }}</dd></div>
            @if ($count->submitted_at)
                <div><dt class="text-slate-500">{{ __('Submitted') }}</dt><dd>{{ $count->submitter?->name }} · {{ $count->submitted_at->format('Y-m-d H:i') }}</dd></div>
            @endif
            @if ($count->approved_at)
                <div><dt class="text-slate-500">{{ __('Approved') }}</dt><dd>{{ $count->approver?->name }} · {{ $count->approved_at->format('Y-m-d H:i') }}</dd></div>
            @endif
            @if ($count->posted_at)
                <div><dt class="text-slate-500">{{ __('Posted') }}</dt><dd>{{ $count->poster?->name }} · {{ $count->posted_at->format('Y-m-d H:i') }}</dd></div>
            @endif
            @if ($count->stockAdjustment)
                <div><dt class="text-slate-500">{{ __('Adjustment ref') }}</dt><dd>{{ $count->stockAdjustment->adjustment_number }}</dd></div>
            @endif
        </dl>
        @if ($count->notes)<p class="text-sm text-slate-600 mb-4">{{ $count->notes }}</p>@endif

        <x-admin.data-table :searchable="true" :exportable="true" export-filename="count-lines">
            <x-slot name="head">
                <tr>
                    <th>{{ __('Item') }}</th>
                    <th>{{ __('System') }}</th>
                    <th>{{ __('Counted') }}</th>
                    <th>{{ __('Variance') }}</th>
                    <th>{{ __('Value') }}</th>
                    <th>{{ __('Reason') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @foreach ($count->items as $line)
                    <tr>
                        <td>{{ $line->inventoryItem?->item_name }}</td>
                        <td>{{ $line->system_quantity }}</td>
                        <td>{{ $line->counted_quantity ?? '—' }}</td>
                        <td>{{ $line->variance_quantity }}</td>
                        <td>{{ number_format((float) $line->variance_value, 2) }}</td>
                        <td>{{ $line->reason }}</td>
                    </tr>
                @endforeach
            </x-slot>
        </x-admin.data-table>
    </x-admin.card>
</x-admin-layout>
