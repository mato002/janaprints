@if (! empty($tabData['restricted']))
    <x-admin.empty-state :title="__('Access restricted')" :description="__('You need production view permission to see this tab.')" />
@else
    @php($jobs = $tabData['jobs'])
    <x-admin.data-table :searchable="false" :exportable="false" :filterable="false">
        <x-slot:head>
            <tr>
                <th>{{ __('Job number') }}</th>
                <th>{{ __('Priority') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Work center') }}</th>
                <th>{{ __('Progress') }}</th>
            </tr>
        </x-slot:head>
        <x-slot:body>
            @forelse ($jobs as $job)
                @php($queue = $job->queues->first())
                @php($progress = (int) ($job->progress_percent ?? 0))
                <tr>
                    <td>
                        <a href="{{ route('admin.production.job-cards.show', $job) }}" class="font-medium text-erp-accent hover:text-erp-accent-hover" data-turbo-frame="erp-main">{{ $job->job_card_number }}</a>
                    </td>
                    <td>{{ $job->priority->value }}</td>
                    <td><x-admin.enum-status-badge :status="$job->status->value" /></td>
                    <td>{{ $queue?->workCenter?->name ?? '—' }}</td>
                    <td>
                        <div class="flex items-center gap-2">
                            <div class="h-1.5 min-w-[4rem] flex-1 rounded-full bg-erp-page">
                                <div class="h-1.5 rounded-full bg-erp-accent" style="width: {{ $progress }}%"></div>
                            </div>
                            <span class="text-xs tabular-nums text-slate-600">{{ $progress }}%</span>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-slate-500 py-6">{{ __('No production jobs for this customer.') }}</td></tr>
            @endforelse
        </x-slot:body>
        @if ($jobs->hasPages())
            <x-slot:footer>
                <x-admin.table-pagination :paginator="$jobs" />
            </x-slot:footer>
        @endif
    </x-admin.data-table>
@endif
