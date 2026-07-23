@php
    use App\Enums\ProductionJobCardStatus;

    $completion = $completion ?? ['eligible' => false, 'blockers' => [], 'blocker_codes' => []];
    $workflowPresentation = $workflowPresentation ?? null;
    $eligible = (bool) ($completion['eligible'] ?? false);

    if (! empty($workflowPresentation['readiness_items'])) {
        $items = $workflowPresentation['readiness_items'];
        $failedCount = (int) ($workflowPresentation['readiness_remaining_count'] ?? 0);
    } else {
        $checklist = collect($readinessChecklist ?? []);
        $hasPostedOutput = (bool) ($hasPostedOutput ?? false);
        $blockerCodes = $completion['blocker_codes'] ?? [];
        $fgWarehouse = $completion['fg_warehouse'] ?? null;

        $operations = $checklist->firstWhere('key', 'operations');
        $qc = $checklist->firstWhere('key', 'qc');
        $materials = $checklist->firstWhere('key', 'materials');

        $items = [];

        $items[] = [
            'passed' => ($operations['state'] ?? null) === 'passed'
                || in_array($jobCard->status, [ProductionJobCardStatus::Completed, ProductionJobCardStatus::ReadyForDispatch], true),
            'label' => __('Production complete'),
            'action' => null,
            'action_label' => null,
            'hint' => null,
        ];

        $items[] = [
            'passed' => ($qc['state'] ?? null) === 'passed',
            'label' => __('QC approved'),
            'action' => ($qc['state'] ?? null) !== 'passed'
                ? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'quality'])
                : null,
            'action_label' => __('Open QC'),
            'hint' => null,
        ];

        $items[] = [
            'passed' => ($materials['state'] ?? null) === 'passed',
            'label' => ($materials['state'] ?? null) === 'passed'
                ? __('Material consumption recorded')
                : __('Material consumption missing'),
            'action' => ($materials['state'] ?? null) !== 'passed'
                ? route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'material-consumption', 'open' => 'record-consumption-modal'])
                : null,
            'action_label' => __('Record consumption'),
            'hint' => null,
        ];

        if (in_array('fg_warehouse', $blockerCodes, true)) {
            $items[] = [
                'passed' => false,
                'label' => __('Finished goods warehouse setup incomplete'),
                'action' => null,
                'action_label' => null,
                'hint' => __('Create a branch for this company, then use Verify defaults on Virtual Locations (Supply Chain → Store Operations).'),
            ];
        } elseif ($fgWarehouse) {
            $items[] = [
                'passed' => true,
                'label' => __('Finished goods warehouse (:code)', ['code' => $fgWarehouse['code']]),
                'action' => null,
                'action_label' => null,
                'hint' => null,
            ];
        }

        if (in_array('stock_role', $blockerCodes, true)) {
            $productItem = $jobCard->inventoryItem;
            $items[] = [
                'passed' => false,
                'label' => __('Product stock role incorrect'),
                'action' => $productItem ? route('admin.inventory.items.edit', $productItem) : route('admin.inventory.items.index'),
                'action_label' => __('Open product'),
                'hint' => null,
            ];
        }

        if ($hasPostedOutput) {
            $items[] = [
                'passed' => true,
                'label' => __('Finished goods posted'),
                'action' => null,
                'action_label' => null,
                'hint' => null,
            ];
        } elseif ($eligible) {
            $items[] = [
                'passed' => true,
                'label' => __('Ready to post finished goods'),
                'action' => null,
                'action_label' => null,
                'hint' => null,
            ];
        }

        $failedCount = collect($items)->where('passed', false)->count();
    }

    $hasPostedOutput = (bool) ($hasPostedOutput ?? false);
    $canCreateDeliveryNote = (bool) ($workflowPresentation['can_create_delivery_note'] ?? false);
@endphp

<x-admin.card class="job-360-readiness-panel h-fit">
    <div class="mb-3 flex items-center justify-between gap-2">
        <h3 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">
            @if ($eligible)
                {{ __('Ready to post') }}
            @else
                {{ __('Readiness') }}
            @endif
        </h3>
        @if ($failedCount > 0)
            <span class="text-xs font-medium text-amber-700">{{ trans_choice(':count requirement remaining|:count requirements remaining', $failedCount, ['count' => $failedCount]) }}</span>
        @endif
    </div>

    <ul class="space-y-2">
        @foreach ($items as $item)
            <li class="flex items-start justify-between gap-2 text-sm">
                <div class="flex min-w-0 items-start gap-2">
                    <span @class([
                        'mt-0.5 shrink-0 font-bold',
                        'text-emerald-600' => $item['passed'],
                        'text-red-600' => ! $item['passed'],
                    ])>{{ $item['passed'] ? '✔' : '✖' }}</span>
                    <div class="min-w-0">
                        <span class="text-slate-800">{{ $item['label'] }}</span>
                        @if (! empty($item['hint'] ?? null))
                            <p class="mt-0.5 text-xs text-slate-500">{{ $item['hint'] }}</p>
                        @endif
                    </div>
                </div>
                @if (! $item['passed'] && ($item['action'] ?? null))
                    <a href="{{ $item['action'] }}" class="shrink-0 text-xs font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ $item['action_label'] }}</a>
                @endif
            </li>
        @endforeach
    </ul>

    @if ($hasPostedOutput)
        <div class="mt-4 border-t border-erp-border pt-3">
            <p class="text-[11px] uppercase tracking-wide text-slate-500">{{ __('Next workflow') }}</p>
            @if ($canCreateDeliveryNote)
                <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']) }}" class="mt-1 inline-flex text-sm font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ __('Create delivery note') }}</a>
            @elseif ($jobCard->status === ProductionJobCardStatus::ReadyForDispatch)
                <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']) }}" class="mt-1 inline-flex text-sm font-medium text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ __('Review dispatch requirements') }}</a>
            @else
                <p class="mt-1 text-sm text-slate-600">{{ __('Dispatch unlocks after finished goods are posted and all dispatch requirements are met.') }}</p>
            @endif
        </div>
    @endif
</x-admin.card>

@if ($jobCard->inventoryItem)
    <x-admin.card class="mt-4 h-fit">
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Inventory summary') }}</h3>
        <dl class="space-y-1 text-sm">
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('SKU') }}</dt><dd class="font-mono text-slate-800">{{ $jobCard->inventoryItem->sku }}</dd></div>
            <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Item') }}</dt><dd class="text-right text-slate-800">{{ $jobCard->inventoryItem->item_name }}</dd></div>
        </dl>
    </x-admin.card>
@endif

<x-admin.card class="mt-4 h-fit">
    <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Job summary') }}</h3>
    <dl class="space-y-1 text-sm">
        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Work center') }}</dt><dd class="text-slate-800">{{ $header['work_center'] ?? '—' }}</dd></div>
        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Due') }}</dt><dd class="text-slate-800">{{ $header['due_date']?->format('Y-m-d') ?? '—' }}</dd></div>
        <div class="flex justify-between gap-2"><dt class="text-slate-500">{{ __('Progress') }}</dt><dd class="tabular-nums text-slate-800">{{ $header['progress_percent'] ?? 0 }}%</dd></div>
    </dl>
</x-admin.card>
