@php
    $presentRow = function ($queue) use ($commandCenter, $workspace, $activeDepartment) {
        if ($commandCenter) {
            return $commandCenter->presentCommandRow($queue, $activeDepartment);
        }

        return $workspace->presentRow($queue);
    };

    $statusColumns = [
        'production_status' => 'production_status_variant',
        'qc_status' => 'qc_status_variant',
        'dispatch_status' => 'dispatch_status_variant',
        'payment_status' => 'payment_status_variant',
        'order_status' => 'order_status_variant',
    ];

    $displayColumns = $columns !== [] ? $columns : [
        ['key' => 'priority_label', 'label' => __('Priority'), 'class' => ''],
        ['key' => 'job_card_number', 'label' => __('Job card'), 'class' => 'font-mono text-xs'],
        ['key' => 'customer_name', 'label' => __('Customer'), 'class' => ''],
        ['key' => 'product', 'label' => __('Product'), 'class' => ''],
        ['key' => 'due_date', 'label' => __('Due'), 'class' => 'tabular-nums'],
        ['key' => 'production_status', 'label' => __('Status'), 'class' => ''],
    ];
    $colCount = count($displayColumns) + 1;
@endphp

<div class="border-b border-erp-border px-4 py-3">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Department operational register') }}</h2>
    <p class="mt-0.5 text-xs text-slate-500">{{ __('Live ERP data — ordered by priority and due date') }}</p>
</div>

<div class="hidden md:block overflow-x-auto production-queue-register">
    <table class="erp-table erp-table--grid production-queue-register-table w-full text-sm">
        <thead>
            <tr>
                @foreach ($displayColumns as $column)
                    <th>{{ $column['label'] }}</th>
                @endforeach
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($queues as $queue)
                @php $row = $presentRow($queue); @endphp
                <tr @class([
                    'bg-red-50/50' => $row['row_tone'] === 'danger',
                    'bg-amber-50/40' => $row['row_tone'] === 'warning',
                ])>
                    @foreach ($displayColumns as $column)
                        @php
                            $key = $column['key'];
                            $value = $row[$key] ?? '—';
                            $variantKey = $statusColumns[$key] ?? null;
                        @endphp
                        <td @class([$column['class'] ?? '', 'whitespace-nowrap' => in_array($key, ['due_date', 'date'], true)])>
                            @if ($key === 'job_card_number' && ! empty($row['job_360_url']))
                                <a href="{{ $row['job_360_url'] }}" class="font-mono text-xs text-erp-primary hover:underline" data-turbo-frame="erp-main">{{ $value }}</a>
                            @elseif ($key === 'product')
                                <div class="font-medium max-w-xs truncate" title="{{ $value }}">{{ $value }}</div>
                                @if (! empty($row['status_badges']))
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach ($row['status_badges'] as $badge)
                                            @include('admin.production.queue.partials.status-badge', [
                                                'label' => $badge['label'],
                                                'variant' => $badge['variant'] ?? 'neutral',
                                            ])
                                        @endforeach
                                    </div>
                                @endif
                            @elseif ($variantKey && filled($value) && $value !== '—')
                                @include('admin.production.queue.partials.status-badge', [
                                    'label' => $value,
                                    'variant' => $row[$variantKey] ?? 'neutral',
                                ])
                            @elseif ($key === 'due_date' && $row['days_remaining'] !== null)
                                {{ $value }}
                                <span @class([
                                    'block text-[10px]',
                                    'text-red-600 font-medium' => $row['days_remaining'] < 0,
                                    'text-slate-500' => $row['days_remaining'] >= 0,
                                ])>{{ $row['days_remaining'] }}d</span>
                            @else
                                {{ $value }}
                            @endif
                        </td>
                    @endforeach
                    <td class="erp-table-actions-col">
                        @include('admin.production.queue.partials.row-actions', ['row' => $row])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $colCount }}">
                        @include('admin.production.queue.partials.empty-state')
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="md:hidden divide-y divide-erp-border">
    @forelse ($queues as $queue)
        @php $row = $presentRow($queue); @endphp
        <div @class([
            'p-4 space-y-2',
            'bg-red-50/50' => $row['row_tone'] === 'danger',
            'bg-amber-50/40' => $row['row_tone'] === 'warning',
        ])>
            <div class="flex items-start justify-between gap-2">
                <div>
                    @if (! empty($row['job_360_url']))
                        <a href="{{ $row['job_360_url'] }}" class="font-mono text-sm font-semibold text-erp-primary" data-turbo-frame="erp-main">{{ $row['job_card_number'] }}</a>
                    @else
                        <p class="font-mono text-sm font-semibold">{{ $row['job_card_number'] }}</p>
                    @endif
                    <p class="text-sm">{{ $row['customer_name'] }}</p>
                    <p class="text-sm font-medium text-erp-primary">{{ $row['product'] }}</p>
                </div>
                @include('admin.production.queue.partials.status-badge', [
                    'label' => $row['production_status'] ?? $row['operational_status'],
                    'variant' => $row['production_status_variant'] ?? $row['operational_variant'] ?? 'neutral',
                ])
            </div>
            @if (! empty($row['status_badges']))
                <div class="flex flex-wrap gap-1">
                    @foreach ($row['status_badges'] as $badge)
                        @include('admin.production.queue.partials.status-badge', [
                            'label' => $badge['label'],
                            'variant' => $badge['variant'] ?? 'neutral',
                        ])
                    @endforeach
                </div>
            @endif
            <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-slate-600">
                <div><dt class="inline">{{ __('Due') }}:</dt> <dd class="inline font-medium">{{ $row['due_date'] ?? '—' }}</dd></div>
                <div><dt class="inline">{{ __('Qty') }}:</dt> <dd class="inline font-medium">{{ $row['quantity'] ?? '—' }}</dd></div>
                <div><dt class="inline">{{ __('Operator') }}:</dt> <dd class="inline font-medium">{{ $row['operator_name'] }}</dd></div>
                <div><dt class="inline">{{ __('Machine') }}:</dt> <dd class="inline font-medium">{{ $row['machine_name'] }}</dd></div>
            </dl>
            <div class="flex flex-wrap gap-2 pt-1">
                @include('admin.production.queue.partials.row-actions', ['row' => $row, 'compact' => true])
            </div>
        </div>
    @empty
        <div class="p-4">
            @include('admin.production.queue.partials.empty-state')
        </div>
    @endforelse
</div>

@if ($queues->hasPages())
    <div class="border-t border-erp-border px-4 py-3">
        {{ $queues->links() }}
    </div>
@endif
