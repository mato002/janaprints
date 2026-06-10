@php
    $outputs = $tabData['outputs'] ?? null;
    $completion = $tabData['completion'] ?? ['eligible' => false, 'blockers' => []];
@endphp

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <div>
        <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production outputs') }}</h3>
        <p class="text-sm text-slate-600">{{ __('Finished goods posted from this job card.') }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        @can('production.outputs.post')
            <button type="button" class="erp-btn-primary text-sm" data-open-dialog="complete-fg-modal">{{ __('Complete to finished goods') }}</button>
        @endcan
        <a href="{{ $tabData['virtual_locations_url'] ?? route('admin.inventory.virtual-locations.index') }}" class="erp-btn-secondary text-sm">{{ __('Virtual locations') }}</a>
        @can('production.outputs.view')
            <a href="{{ route('admin.production.outputs.index') }}" class="erp-link text-sm self-center">{{ __('All outputs') }}</a>
        @endcan
    </div>
</div>

@can('production.outputs.post')
    @include('admin.production.job-cards.workspace.partials.complete-finished-goods-modal', [
        'jobCard' => $jobCard,
        'completion' => $completion,
        'finishedItems' => $tabData['finished_items'] ?? collect(),
    ])
@endcan

<x-admin.data-table>
    <x-slot name="head">
        <tr>
            <th>{{ __('Finished item') }}</th>
            <th>{{ __('Qty completed') }}</th>
            <th>{{ __('Unit cost') }}</th>
            <th>{{ __('Total cost') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Completed') }}</th>
            <th>{{ __('Journal') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($outputs ?? [] as $output)
            <tr>
                <td>{{ $output->finishedItem?->sku }} — {{ $output->finishedItem?->item_name }}</td>
                <td class="tabular-nums">{{ number_format((float) $output->quantity_completed, 3) }}</td>
                <td class="tabular-nums">{{ number_format((float) $output->unit_cost, 4) }}</td>
                <td class="tabular-nums">{{ number_format((float) $output->total_cost, 2) }}</td>
                <td><span class="erp-badge">{{ $output->completion_status->label() }}</span></td>
                <td>{{ $output->completed_at?->format('Y-m-d H:i') ?? '—' }}<br><span class="text-xs text-slate-500">{{ $output->completedByUser?->name }}</span></td>
                <td>{{ $output->postedJournal?->reference ?? '—' }}</td>
            </tr>
        @empty
            <tr><td colspan="7"><x-admin.empty-state icon="cube" :title="__('No production outputs yet')" :description="__('Finished goods will appear after production completion is posted.')" /></td></tr>
        @endforelse
    </x-slot>
    @if ($outputs instanceof \Illuminate\Contracts\Pagination\Paginator)
        <x-slot name="footer"><x-admin.table-pagination :paginator="$outputs" /></x-slot>
    @endif
</x-admin.data-table>
