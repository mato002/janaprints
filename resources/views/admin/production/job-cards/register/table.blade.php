@php
    $indexUrl = route('admin.production.job-cards.index');
    $sortLink = function (string $column) use ($filters, $indexUrl) {
        $direction = ($filters['sort'] ?? '') === $column && ($filters['direction'] ?? 'desc') === 'asc' ? 'desc' : 'asc';
        $query = array_filter([
            ...$filters,
            'sort' => $column,
            'direction' => $direction,
        ], fn ($v) => filled($v) && $v !== false);

        return $indexUrl.'?'.http_build_query($query);
    };
    $showPrimaryEmpty = ($jobCards->total() === 0) && ! ($hasActiveFilters ?? false);
    $columnSortMap = [
        'job_number' => 'job_card_number',
        'customer' => 'customer',
        'priority' => 'priority',
        'due_date' => 'due_date',
        'status' => 'status',
    ];
@endphp

@if ($showPrimaryEmpty)
    @include('admin.production.job-cards.register.empty-state', [
        'canCreate' => $canCreate ?? false,
        'createUrl' => $createUrl ?? null,
        'salesOrdersUrl' => $salesOrdersUrl ?? null,
    ])
@else
    <x-admin.data-table
        :searchable="false"
        :filterable="false"
        export-filename="job-cards-register"
        export-route="admin.production.job-cards.export"
        :export-query="collect($filters)->filter(fn ($value) => filled($value) && $value !== false)->all()"
        :selectable="count($bulkActions) > 0"
        table-id="job-cards-register"
        class="min-w-0"
    >
        @if (count($bulkActions) > 0)
            <x-slot name="bulk">
                @foreach ($bulkActions as $action)
                    @if (($action['key'] ?? '') === 'export')
                        <button type="button" class="erp-btn-secondary text-sm" @click="exportSelected()">{{ $action['label'] }}</button>
                    @endif
                @endforeach
            </x-slot>
        @endif

        <x-slot name="head">
            <tr>
                @if (count($bulkActions) > 0)
                    <th scope="col" class="w-10 erp-table-checkbox-col"><span class="sr-only">{{ __('Select') }}</span></th>
                @endif
                @foreach ($registerColumns as $column)
                    <th
                        scope="col"
                        data-column="{{ $column['key'] }}"
                        x-show="isColumnVisible(@js($column['key']))"
                        x-cloak
                        @class([
                            'hidden lg:table-cell' => in_array($column['key'], ['customer', 'department', 'assigned_team', 'current_stage'], true),
                            'hidden md:table-cell' => in_array($column['key'], ['order', 'quantity', 'product'], true),
                            'hidden sm:table-cell' => $column['key'] === 'priority',
                        ])
                    >
                        @if ($column['sortable'] && isset($columnSortMap[$column['key']]))
                            <a href="{{ $sortLink($columnSortMap[$column['key']]) }}" class="hover:text-erp-accent">{{ $column['label'] }}</a>
                        @else
                            {{ $column['label'] }}
                        @endif
                    </th>
                @endforeach
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>

        <x-slot name="body">
            @forelse ($jobCards as $card)
                @php $row = $indexService->presentRow($card); @endphp
                <tr data-row-id="{{ $card->id }}">
                    @if (count($bulkActions) > 0)
                        <td class="erp-table-checkbox-col">
                            <input type="checkbox" class="row-select rounded border-slate-300" value="{{ $card->id }}" data-export-row />
                        </td>
                    @endif
                    @foreach ($registerColumns as $column)
                        <td
                            data-column="{{ $column['key'] }}"
                            x-show="isColumnVisible(@js($column['key']))"
                            x-cloak
                            @class([
                                'font-mono text-sm font-medium' => $column['key'] === 'job_number',
                                'hidden lg:table-cell' => in_array($column['key'], ['customer', 'department', 'assigned_team', 'current_stage'], true),
                                'hidden md:table-cell' => in_array($column['key'], ['order', 'quantity', 'product'], true),
                                'hidden sm:table-cell' => $column['key'] === 'priority',
                                'max-w-[12rem] truncate' => $column['key'] === 'product',
                                'tabular-nums' => in_array($column['key'], ['quantity', 'due_date'], true),
                                'text-red-700 font-medium' => $column['key'] === 'due_date' && $row['is_delayed'],
                                'capitalize' => $column['key'] === 'priority',
                            ])
                            @if ($column['key'] === 'product')
                                title="{{ $row['product'] }}"
                            @endif
                        >
                            @switch($column['key'])
                                @case('status')
                                    <x-admin.status-badge :variant="$row['badge']['variant']">{{ $row['badge']['label'] }}</x-admin.status-badge>
                                    @break
                                @default
                                    {{ $row[$column['key']] ?? '—' }}
                            @endswitch
                        </td>
                    @endforeach
                    <td class="erp-table-actions-col">
                        <x-admin.table-row-actions>
                            @if ($row['job_360_url'])
                                <x-admin.table-row-action :href="$row['job_360_url']" class="font-semibold text-erp-accent">{{ __('Open Job 360') }}</x-admin.table-row-action>
                            @endif
                            @if ($row['edit_url'])
                                <x-admin.table-row-action :href="$row['edit_url']">{{ __('Edit') }}</x-admin.table-row-action>
                            @endif
                            @foreach ($row['workflow_actions'] as $action)
                                @if (($action['type'] ?? '') === 'post')
                                    <form method="POST" action="{{ $action['url'] }}" class="inline" data-turbo-frame="erp-main">
                                        @csrf
                                        <button type="submit" class="block w-full px-3 py-2 text-left text-sm hover:bg-slate-50">{{ $action['label'] }}</button>
                                    </form>
                                @else
                                    <x-admin.table-row-action :href="$action['url']">{{ $action['label'] }}</x-admin.table-row-action>
                                @endif
                            @endforeach
                        </x-admin.table-row-actions>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($bulkActions) > 0 ? count($registerColumns) + 2 : count($registerColumns) + 1 }}" data-export-skip>
                        <x-admin.empty-state
                            icon="search"
                            :title="__('No job cards match your filters')"
                            :description="__('Adjust filters or clear your search to see more results.')"
                        />
                    </td>
                </tr>
            @endforelse
        </x-slot>

        <x-slot name="footer">
            @if ($jobCards->hasPages())
                <x-admin.table-pagination :paginator="$jobCards" />
            @endif
        </x-slot>
    </x-admin.data-table>
@endif
