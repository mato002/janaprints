<x-admin.card :padding="false">
    <div class="border-b border-erp-border px-4 py-3">
        <h2 class="text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Work Center Register') }}</h2>
        <p class="mt-0.5 text-xs text-slate-500">{{ __('Production capacity and queue status by work center') }}</p>
    </div>
    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Work Center') }}</th>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Stage / Process Area') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th class="text-right">{{ __('Active Jobs') }}</th>
                    <th class="text-right">{{ __('Queue Count') }}</th>
                    <th class="text-right">{{ __('Capacity') }}</th>
                    <th>{{ __('Utilization') }}</th>
                    <th>{{ __('Last Activity') }}</th>
                    <th class="erp-table-actions-col">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dashboard['work_centers'] as $center)
                    @php $row = $workspace->presentCenterRow($center); @endphp
                    <tr>
                        <td class="font-medium">{{ $row['name'] }}</td>
                        <td class="font-mono text-xs">{{ $row['code'] }}</td>
                        <td>{{ $row['stage_name'] }}</td>
                        <td>
                            @if ($row['is_active'])
                                <x-admin.status-badge variant="success">{{ __('Active') }}</x-admin.status-badge>
                            @else
                                <x-admin.status-badge variant="neutral">{{ __('Inactive') }}</x-admin.status-badge>
                            @endif
                        </td>
                        <td class="text-right tabular-nums">{{ $row['active_jobs'] }}</td>
                        <td class="text-right tabular-nums">{{ $row['queue_count'] }}</td>
                        <td class="text-right tabular-nums">{{ $row['capacity'] }}</td>
                        <td class="min-w-[7rem]">
                            <div class="flex flex-col gap-1">
                                <span class="text-xs tabular-nums {{ $row['is_overbooked'] ? 'font-medium text-red-700' : 'text-slate-600' }}">{{ $row['utilization_percent'] }}%</span>
                                <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
                                    <div class="{{ $row['is_overbooked'] ? 'bg-red-500' : 'bg-erp-accent' }} h-full rounded-full" style="width: {{ min(100, $row['utilization_percent']) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap text-xs text-slate-500 tabular-nums">
                            {{ $center->last_activity_at ? \Illuminate\Support\Carbon::parse($center->last_activity_at)->format('Y-m-d H:i') : '—' }}
                        </td>
                        <td class="erp-table-actions-col">
                            <x-admin.table-row-actions>
                                @if ($row['show_url'])
                                    <x-admin.table-row-action :href="$row['show_url']">{{ __('View Work Center') }}</x-admin.table-row-action>
                                @endif
                                @if ($row['queue_url'])
                                    <x-admin.table-row-action :href="$row['queue_url']">{{ __('View Queue') }}</x-admin.table-row-action>
                                @endif
                                @if ($row['scheduling_url'])
                                    <x-admin.table-row-action :href="$row['scheduling_url']">{{ __('View Scheduling') }}</x-admin.table-row-action>
                                @endif
                            </x-admin.table-row-actions>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="py-8 text-center text-slate-500">{{ __('No work centers match your filters.') }}</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($dashboard['work_centers']->hasPages())
        <div class="border-t border-erp-border px-4 py-3">{{ $dashboard['work_centers']->links() }}</div>
    @endif
</x-admin.card>
