<x-admin-layout :title="$adjustment->adjustment_number" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Adjustments'), 'url' => route('admin.inventory.adjustments.index')], ['label' => $adjustment->adjustment_number]]">
    <x-admin.page-header :title="$adjustment->adjustment_number">
        <span class="erp-badge">{{ $adjustment->status->label() }}</span>
        @can('submit', $adjustment)
            <form method="POST" action="{{ route('admin.inventory.adjustments.submit', $adjustment) }}">@csrf
                <button class="erp-btn-secondary">{{ __('Submit for approval') }}</button></form>
        @endcan
        @can('approve', $adjustment)
            <form method="POST" action="{{ route('admin.inventory.adjustments.approve', $adjustment) }}" class="inline-flex items-center gap-2">@csrf
                <input type="text" name="approval_reason" class="erp-toolbar-input" placeholder="{{ __('Approval notes') }}">
                <button class="erp-btn-secondary">{{ __('Approve') }}</button></form>
        @endcan
        @can('post', $adjustment)
            <form method="POST" action="{{ route('admin.inventory.adjustments.post', $adjustment) }}">@csrf
                <button class="erp-btn-primary">{{ __('Post adjustment') }}</button></form>
        @endcan
    </x-admin.page-header>
    <x-admin.card>
        <p class="text-sm text-slate-600 mb-2">{{ $adjustment->reason }}</p>
        @if ($adjustment->submitter)
            <p class="text-xs text-slate-500 mb-2">{{ __('Submitted by :name on :date', ['name' => $adjustment->submitter->name, 'date' => $adjustment->submitted_at?->format('Y-m-d H:i')]) }}</p>
        @endif
        @if ($adjustment->approver)
            <p class="text-xs text-slate-500 mb-2">{{ __('Approved by :name on :date', ['name' => $adjustment->approver->name, 'date' => $adjustment->approved_at?->format('Y-m-d H:i')]) }}</p>
        @endif
        @if ($adjustment->approval_reason)
            <p class="text-xs text-slate-500 mb-2">{{ __('Approval notes: :notes', ['notes' => $adjustment->approval_reason]) }}</p>
        @endif
        @foreach ($adjustment->items as $line)
            <div class="text-sm py-1">{{ $line->inventoryItem?->item_name }}: {{ $line->direction->value }} {{ $line->quantity }}</div>
        @endforeach
    </x-admin.card>
</x-admin-layout>
