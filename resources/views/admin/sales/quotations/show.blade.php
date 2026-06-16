<x-admin-layout :title="$quotation->quotation_number" :breadcrumbs="[['label' => __('Quotations'), 'url' => route('admin.quotations.index')], ['label' => $quotation->quotation_number]]">
    <x-admin.page-header :title="$quotation->quotation_number" :description="$quotation->customer?->company_name">
        <x-slot:actions>
            <span class="erp-badge">{{ str_replace('_', ' ', $quotation->status->value) }}</span>
            <span class="text-sm text-slate-500">Rev {{ $quotation->revision_number }}</span>
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
        </x-slot:actions>
    </x-admin.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <x-admin.kpi-widget :label="__('Subtotal')" :value="$quotation->currency.' '.number_format($quotation->subtotal, 2)" icon="currency-dollar" />
        <x-admin.kpi-widget :label="__('Tax')" :value="$quotation->currency.' '.number_format($quotation->tax_amount, 2)" icon="receipt-tax" />
        <x-admin.kpi-widget :label="__('Total')" :value="$quotation->currency.' '.number_format($quotation->total_amount, 2)" icon="calculator" />
    </div>

    @include('admin.sales.quotations.partials.printing-intelligence-estimate')

    <x-admin.card class="mb-6">
        <h3 class="font-medium mb-3">{{ __('Workflow') }}</h3>
        <div class="flex flex-wrap gap-2">
            @if ($quotation->status === App\Enums\QuotationStatus::Draft)
                @can('transition', $quotation)
                    <form method="POST" action="{{ route('admin.quotations.submit-approval', $quotation) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Submit for approval') }}</button></form>
                @endcan
            @endif
            @if ($quotation->status === App\Enums\QuotationStatus::PendingApproval)
                @can('approve', $quotation)
                    <form method="POST" action="{{ route('admin.quotations.approve', $quotation) }}">@csrf
                        <button class="erp-btn-primary">{{ __('Approve & send') }}</button></form>
                @endcan
                @can('send', $quotation)
                    <form method="POST" action="{{ route('admin.quotations.send', $quotation) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Send') }}</button></form>
                @endcan
            @endif
            @if ($quotation->status === App\Enums\QuotationStatus::Sent)
                @can('transition', $quotation)
                    <form method="POST" action="{{ route('admin.quotations.mark-viewed', $quotation) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Mark viewed') }}</button></form>
                @endcan
            @endif
            @if ($quotation->status === App\Enums\QuotationStatus::Viewed)
                @can('transition', $quotation)
                    <form method="POST" action="{{ route('admin.quotations.accept', $quotation) }}">@csrf
                        <button class="erp-btn-primary">{{ __('Accept') }}</button></form>
                    <form method="POST" action="{{ route('admin.quotations.reject', $quotation) }}">@csrf
                        <button class="erp-btn-secondary text-red-600">{{ __('Reject') }}</button></form>
                @endcan
            @endif
            @if ($quotation->status === App\Enums\QuotationStatus::Accepted)
                @can('convert', $quotation)
                    <form method="POST" action="{{ route('admin.quotations.convert', $quotation) }}">@csrf
                        <button class="erp-btn-primary">{{ __('Convert to sales order') }}</button></form>
                @endcan
            @endif
            @if ($quotation->salesOrder)
                @can('view', $quotation->salesOrder)
                    <a href="{{ route('admin.sales-orders.show', $quotation->salesOrder) }}" class="erp-btn-secondary">{{ __('View sales order') }}</a>
                @endcan
            @endif
        </div>
    </x-admin.card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Line items') }}</h3>
            <table class="erp-table text-sm">
                <thead><tr><th>{{ __('Item') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Total') }}</th></tr></thead>
                <tbody>
                    @foreach ($quotation->items as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ number_format($item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Revision history') }}</h3>
            @foreach ($quotation->revisions as $revision)
                <div class="text-sm border-b py-2">
                    Rev {{ $revision->revision_number }} — {{ $revision->created_at }}
                    <span class="text-slate-400">({{ $revision->creator?->name }})</span>
                </div>
            @endforeach
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Notes') }}</h3>
            @foreach ($quotation->quotationNotes as $note)
                <p class="text-sm border-b py-2">{{ $note->note }} <span class="text-xs text-slate-400">{{ $note->user?->name }}</span></p>
            @endforeach
            <form method="POST" action="{{ route('admin.quotations.notes.store', $quotation) }}" class="mt-3">@csrf
                <textarea name="note" class="erp-input" rows="2" required></textarea>
                <button class="erp-btn-secondary mt-2 text-sm">{{ __('Add note') }}</button>
            </form>
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Attachments') }}</h3>
            @foreach ($quotation->attachments as $file)
                <div class="text-sm flex justify-between py-1">
                    <span>{{ $file->original_name }} ({{ $file->attachment_type->value }})</span>
                </div>
            @endforeach
            <form method="POST" action="{{ route('admin.quotations.attachments.store', $quotation) }}" enctype="multipart/form-data" data-turbo-frame="_top" class="mt-3 space-y-2">@csrf
                <select name="attachment_type" class="erp-input">
                    @foreach (App\Enums\QuotationAttachmentType::cases() as $t)
                        <option value="{{ $t->value }}">{{ $t->name }}</option>
                    @endforeach
                </select>
                <input type="file" name="file" class="text-sm" required>
                <button class="erp-btn-secondary text-sm">{{ __('Upload') }}</button>
            </form>
        </x-admin.card>
    </div>
</x-admin-layout>
