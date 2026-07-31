@php
    $operatorMode = (bool) ($operatorMode ?? false);
@endphp

<div class="production-floor-register__table erp-card erp-table-scroll" x-ref="queueTable">
    <table class="erp-table erp-table--grid production-floor-queue-table w-full text-sm">
        <thead>
            <tr>
                <th class="production-floor-col-select w-10">
                    <input
                        type="checkbox"
                        class="rounded border-slate-300"
                        aria-label="{{ __('Select all jobs on this page') }}"
                        @change="toggleSelectAll($event.target.checked)"
                        :checked="allVisibleSelected"
                        :indeterminate="someVisibleSelected && !allVisibleSelected"
                    >
                </th>
                <th class="production-floor-col-job">{{ __('Job') }}</th>
                <th>{{ __('Customer') }}</th>
                <th>{{ __('Product') }}</th>
                <th>{{ __('Stage') }}</th>
                <th>{{ __('Machine') }}</th>
                <th>{{ __('Vendor') }}</th>
                <th>{{ __('Due') }}</th>
                <th>{{ __('Priority') }}</th>
                <th class="erp-table-actions-col">{{ __('Next step') }}</th>
            </tr>
        </thead>
        <tbody x-ref="queueBody">
            @forelse ($rows as $row)
                @php
                    $rowClasses = ['production-floor-row', 'cursor-pointer', 'hover:bg-slate-50'];
                    if ($row['is_overdue']) {
                        $rowClasses[] = 'production-floor-row--overdue';
                    }
                    if ($row['stage'] === 'qc') {
                        $rowClasses[] = 'production-floor-row--qc';
                    }
                    if ($row['stage'] === 'at_vendor') {
                        $rowClasses[] = 'production-floor-row--vendor';
                    }
                    if ($row['stage'] === 'out') {
                        $rowClasses[] = 'production-floor-row--completed';
                    }
                    if ($row['stage'] === 'on_hold') {
                        $rowClasses[] = 'production-floor-row--hold';
                    }
                @endphp
                <tr
                    class="{{ implode(' ', $rowClasses) }}"
                    data-floor-row
                    data-job-key="{{ $row['public_id'] }}"
                    data-print-path="{{ $row['print_path'] ?? 'floor-display' }}"
                    data-filter-search="{{ strtolower(implode(' ', array_filter([
                        $row['job_number'] ?? '',
                        $row['customer'] ?? '',
                        $row['product'] ?? '',
                        $row['sku'] ?? '',
                    ]))) }}"
                    data-filter-stage="{{ $row['stage'] }}"
                    data-filter-machine-id="{{ $row['machine_id'] ?? '' }}"
                    data-filter-vendor="{{ strtolower(trim($row['vendor'] ?? '')) }}"
                    data-filter-priority="{{ $row['priority'] }}"
                    data-filter-overdue="{{ $row['is_overdue'] ? '1' : '0' }}"
                    data-group-machine="{{ $row['machine'] ?? __('Assign') }}"
                    data-group-stage="{{ $row['stage_label'] }}"
                    data-group-priority="{{ $row['priority_label'] }}"
                    data-group-vendor="{{ $row['vendor'] ?? __('No vendor') }}"
                    data-group-due="{{ $row['required_date'] ?? __('No date') }}"
                    data-group-operator="{{ $row['work_center'] ?? __('Unassigned') }}"
                    data-group-customer="{{ $row['customer'] ?? __('Unknown') }}"
                    :class="{ 'production-floor-row--selected': selectedJobs.includes(@js($row['public_id'])) }"
                    @click="openPanel(@js($row['public_id']))"
                >
                    <td class="production-floor-col-select" @click.stop>
                        <input
                            type="checkbox"
                            class="rounded border-slate-300"
                            aria-label="{{ __('Select job') }} {{ $row['job_number'] }}"
                            :checked="selectedJobs.includes(@js($row['public_id']))"
                            @change="toggleJobSelection(@js($row['public_id']), $event.target.checked)"
                        >
                    </td>
                    <td class="production-floor-col-job font-mono text-xs">
                        <button type="button" class="break-all text-left text-erp-accent hover:underline" @click.stop="openPanel(@js($row['public_id']))">
                            {{ $row['job_number'] }}
                        </button>
                        @if ($row['is_overdue'])
                            <span class="production-floor-row__overdue-badge" title="{{ __('Overdue') }}">
                                <span aria-hidden="true">⏰</span>
                                {{ __('Overdue') }}
                            </span>
                        @endif
                    </td>
                    <td>{{ $row['customer'] ?? '—' }}</td>
                    <td>
                        <span>{{ $row['product'] ?? '—' }}</span>
                        @if ($row['sku'])
                            <span class="block text-[11px] text-slate-500">{{ $row['sku'] }}</span>
                        @endif
                    </td>
                    <td>
                        @include('admin.production.floor.partials.stage-badge', [
                            'stage' => $row['stage'],
                            'label' => $row['stage_label'],
                        ])
                    </td>
                    <td @click.stop @mousedown.stop>
                        @can('machines.assign')
                            @if (count($filter_options['machines']) > 0)
                                <select
                                    name="assigned_machine_asset_id"
                                    class="erp-select production-floor-machine-select w-full text-xs {{ $row['machine_id'] ? 'production-floor-machine-select--assigned' : 'production-floor-machine-select--pending' }}"
                                    data-current-value="{{ $row['machine_id'] ?? '' }}"
                                    @change="assignMachineInline(@js($row['public_id']), $event)"
                                    @click.stop
                                    @mousedown.stop
                                >
                                    <option value="">{{ __('Assign') }}</option>
                                    @foreach ($filter_options['machines'] as $machine)
                                        <option
                                            value="{{ $machine['value'] }}"
                                            @selected((string) $row['machine_id'] === $machine['value'])
                                        >
                                            {{ $machine['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            @elseif ($row['machine'])
                                <span class="text-xs font-medium text-erp-primary">{{ $row['machine'] }}</span>
                            @else
                                <span class="text-xs text-slate-500">{{ __('No machines registered') }}</span>
                            @endif
                        @else
                            {{ $row['machine'] ?? '—' }}
                        @endcan
                    </td>
                    <td class="text-xs">
                        @if ($row['vendor'])
                            <span class="font-medium">{{ $row['vendor'] }}</span>
                            @if ($row['vendor_expected_return'])
                                <span class="block text-slate-500">{{ __('Return') }}: {{ $row['vendor_expected_return'] }}</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="whitespace-nowrap text-xs {{ $row['is_overdue'] ? 'font-semibold text-red-800' : '' }}">
                        {{ $row['required_date'] ?? '—' }}
                    </td>
                    <td>
                        @include('admin.production.floor.partials.priority-badge', [
                            'priority' => $row['priority'],
                            'label' => $row['priority_label'],
                        ])
                    </td>
                    <td class="erp-table-actions-col" @click.stop>
                        @if ($row['primary_action'])
                            @include('admin.production.floor.partials.next-step-action', [
                                'action' => $row['primary_action'],
                                'jobKey' => $row['public_id'],
                                'operatorMode' => $operatorMode,
                                'buttonClass' => 'erp-btn-primary text-xs py-1 px-2',
                            ])
                        @else
                            <button type="button" class="erp-btn-secondary text-xs py-1 px-2" @click="openPanel(@js($row['public_id']))">{{ __('Open') }}</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="py-10 text-center text-slate-500">
                        {{ __('No jobs match the current filters.') }}
                    </td>
                </tr>
            @endforelse
            @if ($rows->isNotEmpty())
                <tr class="production-floor-live-empty" x-ref="liveFilterEmpty" hidden>
                    <td colspan="10" class="py-10 text-center text-slate-500">
                        {{ __('No jobs match the current filters on this page.') }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>
