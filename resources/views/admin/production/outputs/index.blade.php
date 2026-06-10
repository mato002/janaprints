<x-admin-layout :title="__('Production outputs')" :breadcrumbs="[
    ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
    ['label' => __('Production outputs')],
]">
    <x-admin.page-header :title="__('Production outputs')" :description="__('Finished goods completion records posted from production jobs.')" />

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Job card') }}</th>
                <th>{{ __('Finished item') }}</th>
                <th>{{ __('Qty') }}</th>
                <th>{{ __('Unit cost') }}</th>
                <th>{{ __('Total') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Completed') }}</th>
                <th>{{ __('Journal') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($outputs as $output)
                <tr>
                    <td>
                        @if ($output->jobCard)
                            <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $output->jobCard, 'tab' => 'outputs']) }}" class="erp-link">{{ $output->jobCard->job_card_number }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $output->finishedItem?->sku }} — {{ $output->finishedItem?->item_name }}</td>
                    <td class="tabular-nums">{{ number_format((float) $output->quantity_completed, 3) }}</td>
                    <td class="tabular-nums">{{ number_format((float) $output->unit_cost, 4) }}</td>
                    <td class="tabular-nums">{{ number_format((float) $output->total_cost, 2) }}</td>
                    <td>{{ $output->completion_status->label() }}</td>
                    <td>{{ $output->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                    <td>{{ $output->postedJournal?->reference ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="8"><x-admin.empty-state icon="cube" :title="__('No outputs yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$outputs" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
