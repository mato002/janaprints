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
@endphp

<x-admin.data-table
    :searchable="false"
    :filterable="false"
    export-filename="job-cards-command-center"
    :selectable="count($bulkActions) > 0"
    table-id="job-cards-command-center"
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
                    <th scope="col" class="w-10"><span class="sr-only">{{ __('Select') }}</span></th>
                @endif
                <th scope="col"><a href="{{ $sortLink('job_card_number') }}" class="hover:text-erp-accent">{{ __('Job card') }}</a></th>
                <th scope="col" class="hidden lg:table-cell"><a href="{{ $sortLink('customer') }}" class="hover:text-erp-accent">{{ __('Customer') }}</a></th>
                <th scope="col" class="hidden xl:table-cell">{{ __('Product / job') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('Sales order') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Quantity') }}</th>
                <th scope="col"><a href="{{ $sortLink('due_date') }}" class="hover:text-erp-accent">{{ __('Due date') }}</a></th>
                <th scope="col" class="hidden md:table-cell">{{ __('Work center') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Current stage') }}</th>
                <th scope="col"><a href="{{ $sortLink('status') }}" class="hover:text-erp-accent">{{ __('Status') }}</a></th>
                <th scope="col" class="hidden sm:table-cell"><a href="{{ $sortLink('priority') }}" class="hover:text-erp-accent">{{ __('Priority') }}</a></th>
                <th scope="col" class="hidden xl:table-cell"><a href="{{ $sortLink('updated_at') }}" class="hover:text-erp-accent">{{ __('Last updated') }}</a></th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>

        <x-slot name="body">
            @forelse ($jobCards as $card)
                @php $row = $indexService->presentRow($card); @endphp
                <tr>
                    @if (count($bulkActions) > 0)
                        <td>
                            <input type="checkbox" class="row-select rounded border-slate-300" value="{{ $card->id }}" data-export-row />
                        </td>
                    @endif
                    <td class="font-mono text-sm font-medium">{{ $row['job_number'] }}</td>
                    <td class="hidden lg:table-cell">{{ $row['customer'] }}</td>
                    <td class="hidden max-w-[12rem] truncate xl:table-cell" title="{{ $row['product_description'] }}">{{ $row['product_description'] }}</td>
                    <td class="hidden md:table-cell font-mono text-xs">{{ $row['sales_order_number'] }}</td>
                    <td class="hidden lg:table-cell tabular-nums">{{ $row['quantity'] }}</td>
                    <td class="tabular-nums {{ $row['is_delayed'] ? 'text-red-700 font-medium' : '' }}">{{ $row['due_date'] }}</td>
                    <td class="hidden md:table-cell">{{ $row['work_center'] }}</td>
                    <td class="hidden lg:table-cell text-slate-600">{{ $row['current_stage'] }}</td>
                    <td>
                        <x-admin.status-badge :variant="$row['badge']['variant']">{{ $row['badge']['label'] }}</x-admin.status-badge>
                    </td>
                    <td class="hidden sm:table-cell capitalize">{{ $row['priority'] }}</td>
                    <td class="hidden tabular-nums text-slate-500 xl:table-cell">{{ $row['last_updated'] }}</td>
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
                    <td colspan="{{ count($bulkActions) > 0 ? 13 : 12 }}">
                        <x-admin.empty-state icon="collection" :title="__('No job cards match your filters')" :description="__('Adjust filters or create a new job card from a confirmed sales order.')" />
                    </td>
                </tr>
            @endforelse
        </x-slot>

        <x-slot name="footer">
            @if ($jobCards && $jobCards->hasPages())
                <x-admin.table-pagination :paginator="$jobCards" />
            @endif
        </x-slot>
</x-admin.data-table>
