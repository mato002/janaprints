{{-- Sales Order 360 — presentation shell only. Permissions/routes/actions unchanged. --}}
@php
    $activeTab = request()->string('tab')->toString() ?: 'overview';
    $allowedTabs = ['overview', 'commercial', 'production', 'specifications', 'financial', 'notes', 'attachments'];
    if (! in_array($activeTab, $allowedTabs, true)) {
        $activeTab = 'overview';
    }

    $statusLabel = str_replace('_', ' ', $salesOrder->status->value);
    $financialLabel = $financial['financial_status_label'] ?? null;
    $financialVariant = $financial['financial_status_variant'] ?? 'slate';
@endphp

<x-admin-layout
    :title="$salesOrder->order_number"
    :breadcrumbs="[
        ['label' => __('Sales Orders'), 'url' => route('admin.sales-orders.dashboard')],
        ['label' => $salesOrder->order_number],
    ]"
>
    <div
        class="so-360"
        x-data="{
            tab: @js($activeTab),
            setTab(id) {
                this.tab = id;
                const url = new URL(window.location.href);
                url.searchParams.set('tab', id);
                window.history.replaceState({}, '', url);
            },
        }"
    >
        @include('admin.sales.orders.workspace.header')

        @include('admin.sales.orders.workspace.lifecycle-rail')

        <nav class="so-360__tabs" aria-label="{{ __('Sales order workspace tabs') }}">
            @foreach ([
                'overview' => __('Overview'),
                'commercial' => __('Commercial'),
                'production' => __('Production'),
                'specifications' => __('Specifications'),
                'financial' => __('Financial'),
                'notes' => __('Notes'),
                'attachments' => __('Attachments'),
            ] as $id => $label)
                <button
                    type="button"
                    class="so-360__tab"
                    :class="tab === @js($id) && 'so-360__tab--active'"
                    @click="setTab(@js($id))"
                    :aria-selected="tab === @js($id)"
                >{{ $label }}</button>
            @endforeach
        </nav>

        <div class="so-360__panels">
            <div x-show="tab === 'overview'" class="so-360__panel">
                @include('admin.sales.orders.workspace.tabs.overview')
            </div>
            <div x-show="tab === 'commercial'" x-cloak class="so-360__panel">
                @include('admin.sales.orders.workspace.tabs.commercial')
            </div>
            <div x-show="tab === 'production'" x-cloak class="so-360__panel">
                @include('admin.sales.orders.workspace.tabs.production')
            </div>
            <div x-show="tab === 'specifications'" x-cloak class="so-360__panel">
                @include('admin.sales.orders.workspace.tabs.specifications')
            </div>
            <div x-show="tab === 'financial'" x-cloak class="so-360__panel">
                @include('admin.sales.orders.workspace.tabs.financial')
            </div>
            <div x-show="tab === 'notes'" x-cloak class="so-360__panel">
                @include('admin.sales.orders.workspace.tabs.notes')
            </div>
            <div x-show="tab === 'attachments'" x-cloak class="so-360__panel">
                @include('admin.sales.orders.workspace.tabs.attachments')
            </div>
        </div>

        @include('admin.sales.orders.workspace.mobile-action-bar')
    </div>
</x-admin-layout>
