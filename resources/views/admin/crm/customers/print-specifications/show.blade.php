@php
    $header = $workspace['header'];
    $activeTab = $workspace['active_tab'];
    $tabData = $workspace['tab_data'];
@endphp

<x-admin-layout
    :title="$header['name']"
    :breadcrumbs="[
        ['label' => __('Customers'), 'url' => route('admin.crm.customers.index')],
        ['label' => $customer->company_name, 'url' => route('admin.crm.customers.show', ['customer' => $customer, 'tab' => 'print-specifications'])],
        ['label' => $header['code']],
    ]"
>
    <div class="spec-workspace w-full min-w-0 space-y-4">
        @include('admin.crm.customers.print-specifications.workspace.header', [
            'customer' => $customer,
            'specification' => $specification,
            'workspace' => $workspace,
        ])

        @include('admin.crm.customers.print-specifications.workspace.summary-strip', [
            'items' => $workspace['summary_strip'],
        ])

        @if (! empty($workspace['live_reference_warnings']))
            <div class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                @foreach ($workspace['live_reference_warnings'] as $warning)
                    <p>{{ $warning }}</p>
                @endforeach
            </div>
        @endif

        @include('admin.crm.customers.print-specifications.workspace.tabs-nav', [
            'tabs' => $workspace['tabs'],
        ])

        <div class="spec-workspace__panel rounded-lg border border-erp-border bg-white p-4">
            @include('admin.crm.customers.print-specifications.workspace.tabs.' . str_replace('-', '_', $activeTab), [
                'customer' => $customer,
                'specification' => $specification,
                'workspace' => $workspace,
                'tabData' => $tabData,
            ])
        </div>
    </div>
</x-admin-layout>
