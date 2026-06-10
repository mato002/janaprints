<x-admin-layout :title="__('COGS postings')" :breadcrumbs="[
    ['label' => __('Dispatch'), 'url' => route('admin.dispatch.dashboard')],
    ['label' => __('COGS postings')],
]">
    <x-admin.page-header :title="__('COGS posting report')" :description="__('Delivery notes with Dr COGS / Cr Finished Goods journals posted on delivery confirmation.')" />

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Delivery note') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Items') }}</th>
                <th>{{ __('Total cost') }}</th>
                <th>{{ __('Delivered') }}</th>
                <th>{{ __('Journal') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($notes as $note)
                <tr>
                    <td><a href="{{ route('admin.dispatch.delivery-notes.show', $note) }}" class="erp-link">{{ $note->delivery_note_number }}</a></td>
                    <td>{{ $note->customer?->company_name }}</td>
                    <td>{{ $note->items->pluck('inventoryItem.sku')->filter()->join(', ') ?: $note->items->count().' '.__('lines') }}</td>
                    <td class="tabular-nums">{{ number_format($note->items->sum(fn ($l) => (float) ($l->total_cost ?? 0)), 2) }}</td>
                    <td>{{ $note->delivered_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td class="font-mono">{{ $note->postedJournal?->reference ?? $note->postedJournal?->journal_number ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state icon="document-text" :title="__('No COGS postings yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$notes" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
