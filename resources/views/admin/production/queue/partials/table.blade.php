@php
    use App\Support\Navigation\WorkspaceEmbed;

    $detailLinkAttrs = WorkspaceEmbed::leaveWorkspaceLinkAttributes();
    $turboFrame = WorkspaceEmbed::turboFrame();

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

    $frozenColumnKeys = ['date', 'job_card_number', 'customer_name'];

    $displayColumns = $columns !== [] ? $columns : [
        ['key' => 'priority_label', 'label' => __('Priority'), 'class' => ''],
        ['key' => 'job_card_number', 'label' => __('Job card'), 'class' => 'font-mono text-xs'],
        ['key' => 'customer_name', 'label' => __('Customer'), 'class' => ''],
        ['key' => 'product', 'label' => __('Product'), 'class' => ''],
        ['key' => 'due_date', 'label' => __('Due'), 'class' => 'tabular-nums text-right'],
        ['key' => 'order_status', 'label' => __('Status'), 'class' => ''],
    ];
    $colCount = count($displayColumns) + 1;

    $frozenIndex = 0;
@endphp

@unless ($dense ?? false)
    <div class="border-b border-erp-border px-4 py-2">
        <h2 class="text-xs font-semibold uppercase tracking-wide text-erp-primary">{{ __('Department operational register') }}</h2>
        <p class="text-[11px] text-slate-500">{{ __('Live ERP data — ordered by priority and due date') }}</p>
    </div>
@endunless

<div class="hidden md:block production-queue-register production-queue-register--scroll">
    <table class="erp-table erp-table--grid production-queue-register-table w-full text-sm">
        <thead>
            <tr>
                @foreach ($displayColumns as $column)
                    @php
                        $isFrozen = in_array($column['key'], $frozenColumnKeys, true);
                        $frozenClass = $isFrozen ? 'production-queue-col-frozen production-queue-col-frozen--'.(++$frozenIndex) : '';
                    @endphp
                    <th @class([$frozenClass, 'text-right' => str_contains($column['class'] ?? '', 'text-right')])>{{ $column['label'] }}</th>
                @endforeach
                <th class="erp-table-actions-col production-queue-col-actions">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($queues as $queue)
                @php $row = $presentRow($queue); @endphp
                <tr @class([
                    'production-queue-row',
                    'production-queue-row--'.$row['row_urgency'] => ($row['row_urgency'] ?? 'default') !== 'default',
                ])>
                    @php $frozenIndex = 0; @endphp
                    @foreach ($displayColumns as $column)
                        @php
                            $key = $column['key'];
                            $value = $row[$key] ?? '—';
                            $variantKey = $statusColumns[$key] ?? null;
                            $isFrozen = in_array($key, $frozenColumnKeys, true);
                            $frozenClass = $isFrozen ? 'production-queue-col-frozen production-queue-col-frozen--'.(++$frozenIndex) : '';
                        @endphp
                        <td @class([
                            $column['class'] ?? '',
                            $frozenClass,
                            'whitespace-nowrap' => in_array($key, ['due_date', 'date', 'date_sent', 'expected_return'], true),
                        ])>
                            @if ($key === 'job_card_number' && ! empty($row['job_360_url']))
                                <a href="{{ $row['job_360_url'] }}" class="font-mono text-xs text-erp-primary hover:underline" @foreach ($detailLinkAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>{{ $value }}</a>
                            @elseif ($key === 'product')
                                <div class="production-queue-product" title="{{ $value }}">{{ $value }}</div>
                            @elseif ($key === 'progress')
                                @php $percent = (int) ($row['progress_percent'] ?? 0); @endphp
                                <div class="production-queue-progress" title="{{ __(':percent% complete', ['percent' => $percent]) }}">
                                    <div class="production-queue-progress__track">
                                        <div class="production-queue-progress__fill" style="width: {{ $percent }}%"></div>
                                    </div>
                                    <span class="production-queue-progress__label tabular-nums">{{ $percent }}%</span>
                                </div>
                            @elseif ($variantKey && filled($value) && $value !== '—')
                                <div class="space-y-1">
                                    @include('admin.production.queue.partials.status-badge', [
                                        'label' => $value,
                                        'variant' => $row[$variantKey] ?? 'neutral',
                                    ])
                                    @if (in_array($key, ['order_status', 'production_status'], true) && ! empty($row['status_badges']))
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($row['status_badges'] as $badge)
                                                @include('admin.production.queue.partials.status-badge', [
                                                    'label' => $badge['label'],
                                                    'variant' => $badge['variant'] ?? 'neutral',
                                                ])
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
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
                    <td class="erp-table-actions-col production-queue-col-actions">
                        @include('admin.production.queue.partials.row-actions', ['row' => $row, 'inline' => true])
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
            'p-4 space-y-2 production-queue-row',
            'production-queue-row--'.$row['row_urgency'] => ($row['row_urgency'] ?? 'default') !== 'default',
        ])>
            <div class="flex items-start justify-between gap-2">
                <div>
                    @if (! empty($row['job_360_url']))
                        <a href="{{ $row['job_360_url'] }}" class="font-mono text-sm font-semibold text-erp-primary" @foreach ($detailLinkAttrs as $attr => $val) {{ $attr }}="{{ $val }}" @endforeach>{{ $row['job_card_number'] }}</a>
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
            @php $percent = (int) ($row['progress_percent'] ?? 0); @endphp
            <div class="production-queue-progress production-queue-progress--mobile">
                <div class="production-queue-progress__track">
                    <div class="production-queue-progress__fill" style="width: {{ $percent }}%"></div>
                </div>
                <span class="production-queue-progress__label tabular-nums">{{ $percent }}%</span>
            </div>
            <dl class="grid grid-cols-2 gap-x-3 gap-y-1 text-xs text-slate-600">
                <div><dt class="inline">{{ __('Due') }}:</dt> <dd class="inline font-medium">{{ $row['due_date'] ?? '—' }}</dd></div>
                <div><dt class="inline">{{ __('Qty') }}:</dt> <dd class="inline font-medium">{{ $row['quantity'] ?? '—' }}</dd></div>
                <div><dt class="inline">{{ __('Operator') }}:</dt> <dd class="inline font-medium">{{ $row['operator_name'] }}</dd></div>
                <div><dt class="inline">{{ __('Machine') }}:</dt> <dd class="inline font-medium">{{ $row['machine_name'] }}</dd></div>
            </dl>
            <div class="flex flex-wrap gap-2 pt-1">
                @include('admin.production.queue.partials.row-actions', ['row' => $row, 'compact' => true, 'inline' => true])
            </div>
        </div>
    @empty
        <div class="p-4">
            @include('admin.production.queue.partials.empty-state')
        </div>
    @endforelse
</div>

@if ($queues->hasPages())
    <x-admin.table-pagination :paginator="$queues" :turbo-frame="$turboFrame" />
@endif
