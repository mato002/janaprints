@if (! ($embeddedInDesk ?? false))
    <x-admin.page-header :title="__('Quotations')">
        @can('create', App\Models\Sales\Quotation::class)
            <x-admin.form-modal-link :href="route('admin.quotations.create')">{{ __('Create') }}</x-admin.form-modal-link>
        @endcan
    </x-admin.page-header>
@else
    <div class="mb-3 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-sm font-semibold text-erp-primary">{{ __('Quotations') }}</h2>
            <p class="text-xs text-slate-600">{{ __('Quote register for this branch.') }}</p>
        </div>
        @can('create', App\Models\Sales\Quotation::class)
            <x-admin.form-modal-link :href="route('admin.quotations.create')">{{ __('Create') }}</x-admin.form-modal-link>
        @endcan
    </div>
@endif

<x-admin.data-table
    :search-placeholder="__('Search quotations…')"
    export-filename="quotations"
    :chips="[
        ['id' => 'all', 'label' => __('All')],
        ['id' => 'draft', 'label' => __('Draft')],
        ['id' => 'pending_approval', 'label' => __('Pending')],
        ['id' => 'sent', 'label' => __('Sent')],
        ['id' => 'approved', 'label' => __('Approved')],
    ]"
>
    <x-slot name="head">
        <tr>
            <th scope="col">{{ __('Number') }}</th>
            <th scope="col">{{ __('Customer') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Date') }}</th>
            <th scope="col">{{ __('Total') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($quotations as $quotation)
            @php
                $search = strtolower($quotation->quotation_number.' '.($quotation->customer?->company_name ?? '').' '.$quotation->status->value);
                $chip = strtolower($quotation->status->value);
            @endphp
            <tr x-show="rowVisible(@js($search), @js($chip))">
                <td class="font-medium">
                    <a href="{{ route('admin.quotations.show', $quotation) }}" class="text-erp-accent hover:underline" data-turbo-frame="erp-main" data-turbo-action="advance">{{ $quotation->quotation_number }}</a>
                </td>
                <td>{{ $quotation->customer?->company_name ?? '—' }}</td>
                <td class="hidden md:table-cell">{{ $quotation->quotation_date->format('Y-m-d') }}</td>
                <td class="tabular-nums">{{ $quotation->currency }} {{ number_format($quotation->total_amount, 2) }}</td>
                <td><x-admin.enum-status-badge :status="$quotation->status->value" /></td>
                <td class="erp-table-actions-col">
                    @include('admin.sales.quotations.partials.row-actions', ['quotation' => $quotation])
                </td>
            </tr>
        @empty
            <tr><td colspan="6"><x-admin.empty-state icon="document-text" :title="__('No quotations yet')" /></td></tr>
        @endforelse
    </x-slot>
    <x-slot name="footer"><x-admin.table-pagination :paginator="$quotations" /></x-slot>
</x-admin.data-table>
