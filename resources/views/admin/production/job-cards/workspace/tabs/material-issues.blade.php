@php
    $issues = $tabData['issues'] ?? null;
    $requirements = $tabData['requirements'] ?? collect();
@endphp

@if ($tabData['can_issue'] ?? false)
    <x-admin.card class="mb-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Issue materials') }}</h3>
            <form method="POST" action="{{ route('admin.production.job-cards.materials.issue-all', $jobCard) }}">
                @csrf
                <button type="submit" class="erp-btn-primary text-sm">{{ __('Issue all remaining') }}</button>
            </form>
        </div>
        @if ($requirements->isNotEmpty())
            <div class="mt-3 overflow-x-auto">
                <table class="erp-table w-full text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Material') }}</th>
                            <th>{{ __('Required') }}</th>
                            <th>{{ __('Issued') }}</th>
                            <th>{{ __('Remaining') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requirements as $row)
                            @if (($row['requirement']->remainingToIssue() ?? 0) > 0)
                                <tr>
                                    <td>{{ $row['item_name'] }}</td>
                                    <td class="tabular-nums">{{ $row['required'] }}</td>
                                    <td class="tabular-nums">{{ $row['issued'] }}</td>
                                    <td class="tabular-nums">{{ $row['requirement']->remainingToIssue() }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.production.job-cards.materials.issue', [$jobCard, $row['requirement']]) }}" class="inline-flex gap-1">
                                            @csrf
                                            <input type="number" step="0.001" name="quantity" class="erp-input w-20 text-xs" placeholder="{{ __('Qty') }}">
                                            <button type="submit" class="erp-btn-ghost text-xs">{{ __('Issue') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-admin.card>
@endif

<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Issue history') }}</h3>
    @if ($issues && $issues->count() > 0)
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Material') }}</th>
                        <th>{{ __('Qty') }}</th>
                        <th>{{ __('Unit cost') }}</th>
                        <th>{{ __('Warehouse') }}</th>
                        <th>{{ __('Issued by') }}</th>
                        <th>{{ __('Issued at') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($issues as $issue)
                        <tr>
                            <td>{{ $issue->inventoryItem?->item_name }} <span class="text-slate-500">({{ $issue->inventoryItem?->sku }})</span></td>
                            <td class="tabular-nums">{{ $issue->quantity }} {{ $issue->inventoryItem?->unitOfMeasure?->code }}</td>
                            <td class="tabular-nums">{{ number_format((float) $issue->unit_cost, 2) }}</td>
                            <td>{{ $issue->warehouse?->name }}</td>
                            <td>{{ $issue->issuer?->name }}</td>
                            <td>{{ $issue->issued_at?->format('Y-m-d H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($issues->hasPages())
            <div class="mt-4">{{ $issues->links() }}</div>
        @endif
    @else
        <x-admin.empty-state :title="__('No issues recorded')" :description="__('Issue materials to production before consumption.')" />
    @endif
</x-admin.card>
