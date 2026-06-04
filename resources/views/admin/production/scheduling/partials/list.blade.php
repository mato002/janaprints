<x-admin.data-table :searchable="false" :exportable="false">
    <x-slot:head>
        <tr>
            <th scope="col">{{ __('Job Number') }}</th>
            <th scope="col">{{ __('Customer') }}</th>
            <th scope="col" class="hidden lg:table-cell">{{ __('Work Center') }}</th>
            <th scope="col">{{ __('Status') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Priority') }}</th>
            <th scope="col" class="hidden sm:table-cell">{{ __('Planned Start') }}</th>
            <th scope="col" class="hidden sm:table-cell">{{ __('Planned End') }}</th>
            <th scope="col" class="hidden md:table-cell">{{ __('Due Date') }}</th>
            <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
        </tr>
    </x-slot:head>
    <x-slot:body>
        @forelse ($jobs as $job)
            @php
                $centers = $workspace->workCenterNames($job);
                $dueDate = $job->planned_end_date ?? $job->salesOrder?->required_date;
                $isOverdue = $job->isDelayed();
                $missingSchedule = ! $job->planned_start_date || ! $job->planned_end_date;
            @endphp
            <tr class="{{ $isOverdue ? 'bg-red-50/40' : ($missingSchedule ? 'bg-amber-50/30' : '') }}">
                <td class="font-mono text-sm font-medium">{{ $job->job_card_number }}</td>
                <td>{{ $job->customer?->company_name ?? '—' }}</td>
                <td class="hidden lg:table-cell">{{ $centers !== [] ? implode(', ', $centers) : '—' }}</td>
                <td><x-admin.enum-status-badge :status="$job->status->value" /></td>
                <td class="hidden md:table-cell capitalize">{{ str_replace('_', ' ', $job->priority->value) }}</td>
                <td class="hidden sm:table-cell tabular-nums">{{ $job->planned_start_date?->format('M j, Y') ?? '—' }}</td>
                <td class="hidden sm:table-cell tabular-nums">{{ $job->planned_end_date?->format('M j, Y') ?? '—' }}</td>
                <td class="hidden md:table-cell tabular-nums {{ $isOverdue ? 'text-red-700 font-medium' : '' }}">
                    {{ $dueDate?->format('M j, Y') ?? '—' }}
                </td>
                <td class="erp-table-actions-col">
                    <x-admin.table-row-actions>
                        @if (Route::has('admin.production.job-cards.show'))
                            <x-admin.table-row-action :href="route('admin.production.job-cards.show', $job)">
                                {{ __('View Job 360') }}
                            </x-admin.table-row-action>
                        @endif
                        @can('schedule', $job)
                            @if (Route::has('admin.production.job-cards.show'))
                                <x-admin.table-row-action :href="route('admin.production.job-cards.show', $job)">
                                    {{ __('Schedule') }}
                                </x-admin.table-row-action>
                            @endif
                        @endcan
                        @can('update', $job)
                            @if (Route::has('admin.production.job-cards.edit'))
                                <x-admin.table-row-action :href="route('admin.production.job-cards.edit', $job)">
                                    {{ __('Edit job') }}
                                </x-admin.table-row-action>
                            @endif
                        @endcan
                    </x-admin.table-row-actions>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9">
                    <x-admin.empty-state
                        icon="calendar"
                        :title="__('No jobs match your filters')"
                        :description="__('Set planned dates on job cards to appear in scheduling, or clear filters to see all schedulable jobs.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot:body>
    @if ($jobs->hasPages())
        <x-slot:footer><x-admin.table-pagination :paginator="$jobs" /></x-slot:footer>
    @endif
</x-admin.data-table>
