@php
    use App\Support\Sales\SalesDeskViews;

    $fromSalesDesk = request('from') === 'sales-desk';
    $breadcrumbs = $fromSalesDesk
        ? [
            ['label' => __('Sales Desk'), 'url' => SalesDeskViews::quotesUrl()],
            ['label' => $quotation->quotation_number],
        ]
        : [
            ['label' => __('Quotations'), 'url' => route('admin.quotations.index')],
            ['label' => $quotation->quotation_number],
        ];
@endphp

<x-admin-layout :title="$quotation->quotation_number" :breadcrumbs="$breadcrumbs">
    {{-- 1. Header: identity, status, workflow + document actions --}}
    <header class="mb-6">
        @if ($fromSalesDesk)
            <a
                href="{{ SalesDeskViews::quotesUrl() }}"
                class="mb-3 inline-flex items-center text-sm text-slate-600 hover:text-erp-primary"
                data-turbo-frame="erp-main"
                data-turbo-action="advance"
            >← {{ __('Back to Sales Desk') }}</a>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-dashboard-title font-mono text-erp-primary">{{ $quotation->quotation_number }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ __('Revision :n', ['n' => $quotation->revision_number]) }}</p>
            </div>

            <div class="flex shrink-0 flex-wrap items-center justify-end gap-2">
                <x-admin.enum-status-badge :status="$quotation->status->value" />

                @include('admin.sales.quotations.partials.show-workflow-actions')

                @can('view', $quotation)
                    <a href="{{ route('admin.quotations.document', $quotation) }}" class="erp-btn-secondary">{{ __('View document') }}</a>
                    <x-documents.pdf-download-button
                        :url="route('admin.quotations.document.pdf', $quotation)"
                        :filename="$quotation->quotation_number"
                        class="erp-btn-secondary"
                    />
                @endcan
                @can('update', $quotation)
                    <a href="{{ route('admin.quotations.edit', $quotation) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
                @endcan
            </div>
        </div>
    </header>

    <x-admin.workflow-error />

    {{-- 2. Customer / quote summary --}}
    @include('admin.sales.quotations.partials.show-summary')

    {{-- 3. Line items + pricing (central commercial section) --}}
    @include('admin.sales.quotations.partials.show-line-items')

    @include('admin.sales.quotations.partials.printing-intelligence-estimate')

    {{-- 4. Production + history (two-column supporting panel) --}}
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('admin.sales.quotations.partials.artwork-link', ['variant' => 'panel'])
        @include('admin.sales.quotations.partials.show-activity')
    </div>

    {{-- 5. Notes --}}
    @include('admin.sales.quotations.partials.show-notes')
</x-admin-layout>
