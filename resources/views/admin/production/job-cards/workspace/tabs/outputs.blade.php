@php
    $outputs = $tabData['outputs'] ?? null;
    $completion = $tabData['completion'] ?? ['eligible' => false, 'blockers' => []];
    $header = $header ?? [];
@endphp

<div class="job-360-outputs-workspace">
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-12">
        <div class="xl:col-span-8 space-y-4">
            @can('production.outputs.post')
                @include('admin.production.job-cards.workspace.partials.finished-goods-post-form', [
                    'jobCard' => $jobCard,
                    'completion' => $completion,
                    'finishedItems' => $tabData['finished_items'] ?? collect(),
                    'workflowPresentation' => $workflowPresentation ?? null,
                ])
            @else
                <x-admin.card>
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Finished goods output') }}</h3>
                    <p class="mt-1 text-sm text-slate-600">{{ __('You do not have permission to post finished goods.') }}</p>
                </x-admin.card>
            @endcan

            <x-admin.card>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Output history') }}</h3>
                </div>

                <x-admin.data-table>
                    <x-slot name="head">
                        <tr>
                            <th>{{ __('Finished item') }}</th>
                            <th>{{ __('Quantity') }}</th>
                            <th>{{ __('Rejected') }}</th>
                            <th>{{ __('Warehouse') }}</th>
                            <th>{{ __('Journal') }}</th>
                            <th>{{ __('Posted by') }}</th>
                            <th>{{ __('Posted time') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </x-slot>
                    <x-slot name="body">
                        @forelse ($outputs ?? [] as $output)
                            <tr>
                                <td>
                                    <span class="font-mono text-xs text-slate-500">{{ $output->finishedItem?->sku }}</span><br>
                                    {{ $output->finishedItem?->item_name }}
                                </td>
                                <td class="tabular-nums">{{ number_format((float) $output->quantity_completed, 3) }}</td>
                                <td class="tabular-nums">{{ number_format((float) ($output->quantity_rejected ?? 0), 3) }}</td>
                                <td>{{ $output->finishedWarehouse?->name ?? __('Finished goods') }}</td>
                                <td class="font-mono text-xs">{{ $output->postedJournal?->reference ?? '—' }}</td>
                                <td>{{ $output->completedByUser?->name ?? '—' }}</td>
                                <td class="whitespace-nowrap">{{ $output->completed_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                <td><span class="erp-badge">{{ $output->completion_status->label() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="8"><x-admin.empty-state icon="cube" :title="__('No production outputs yet')" :description="__('Finished goods will appear after production completion is posted.')" /></td></tr>
                        @endforelse
                    </x-slot>
                    @if ($outputs instanceof \Illuminate\Contracts\Pagination\Paginator)
                        <x-slot name="footer"><x-admin.table-pagination :paginator="$outputs" /></x-slot>
                    @endif
                </x-admin.data-table>
            </x-admin.card>
        </div>

        <aside class="xl:col-span-4">
            @include('admin.production.job-cards.workspace.partials.finished-goods-readiness-panel', [
                'jobCard' => $jobCard,
                'completion' => $completion,
                'readinessChecklist' => $tabData['readiness_checklist'] ?? [],
                'hasPostedOutput' => $tabData['has_posted_output'] ?? false,
                'header' => $header,
                'workflowPresentation' => $workflowPresentation ?? null,
            ])
        </aside>
    </div>
</div>

@can('production.outputs.post')
    @include('admin.production.job-cards.workspace.partials.complete-finished-goods-modal', [
        'jobCard' => $jobCard,
        'completion' => $completion,
        'finishedItems' => $tabData['finished_items'] ?? collect(),
    ])
@endcan
