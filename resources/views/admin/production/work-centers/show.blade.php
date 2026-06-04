@php
    $barWidth = min(100, $metrics['utilization_percent'] ?? 0);
    $barClass = ($metrics['is_overbooked'] ?? false)
        ? 'bg-red-500'
        : (($metrics['utilization_percent'] ?? 0) >= 80 ? 'bg-amber-500' : 'bg-erp-accent');
@endphp

<x-admin-layout
    :title="$workCenter->name"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Work centers'), 'url' => route('admin.production.work-centers.index')],
        ['label' => $workCenter->name],
    ]"
>
    <x-admin.page-header :title="$workCenter->name" :description="$workCenter->code">
        <x-slot name="actions">
            <a href="{{ route('admin.production.work-centers.index') }}" class="erp-btn-secondary">{{ __('Back to list') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <x-admin.card>
            <h3 class="mb-2 text-sm font-semibold text-erp-primary">{{ __('Overview') }}</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Name') }}</dt>
                    <dd class="text-right font-medium">{{ $workCenter->name }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Code') }}</dt>
                    <dd class="font-mono">{{ $workCenter->code }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Stage') }}</dt>
                    <dd>{{ $metrics['stage_name'] ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Status') }}</dt>
                    <dd>
                        @if ($workCenter->is_active)
                            <span class="erp-badge bg-emerald-100 text-emerald-800">{{ __('Active') }}</span>
                        @else
                            <span class="erp-badge bg-slate-100 text-slate-700">{{ __('Inactive') }}</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-2 text-sm font-semibold text-erp-primary">{{ __('Capacity') }}</h3>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Planning slots') }}</dt>
                    <dd class="font-semibold tabular-nums">{{ $metrics['capacity'] ?? $defaultCapacity }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Active jobs') }}</dt>
                    <dd class="font-semibold tabular-nums">{{ $metrics['active_jobs'] ?? 0 }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Queue entries') }}</dt>
                    <dd class="font-semibold tabular-nums">{{ $metrics['queue_count'] ?? 0 }}</dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">{{ __('Utilization') }}</dt>
                    <dd class="font-semibold tabular-nums">{{ $metrics['utilization_percent'] ?? 0 }}%</dd>
                </div>
            </dl>
            <div class="mt-3 h-2 overflow-hidden rounded-full bg-slate-100">
                <div class="{{ $barClass }} h-full rounded-full transition-all" style="width: {{ $barWidth }}%"></div>
            </div>
            @if ($metrics['is_overbooked'] ?? false)
                <p class="mt-2 text-sm text-red-700">{{ __('This work center is overbooked against planning capacity.') }}</p>
            @endif
        </x-admin.card>

        <x-admin.card>
            <h3 class="mb-2 text-sm font-semibold text-erp-primary">{{ __('Description') }}</h3>
            <p class="text-sm text-slate-600">{{ $workCenter->description ?: __('No description provided.') }}</p>
        </x-admin.card>
    </div>

    <x-admin.data-table :searchable="false" :exportable="false">
        <x-slot:head>
            <tr>
                <th scope="col">{{ __('Job number') }}</th>
                <th scope="col">{{ __('Customer') }}</th>
                <th scope="col">{{ __('Queue position') }}</th>
                <th scope="col">{{ __('Status') }}</th>
                <th scope="col" class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot:head>
        <x-slot:body>
            @forelse ($activeQueues as $queue)
                @php
                    $job = $queue->jobCard;
                @endphp
                <tr>
                    <td class="font-mono text-sm">{{ $job?->job_card_number ?? '—' }}</td>
                    <td>{{ $job?->customer?->company_name ?? '—' }}</td>
                    <td class="tabular-nums">{{ $queue->queue_position }}</td>
                    <td><x-admin.enum-status-badge :status="$queue->status->value" /></td>
                    <td class="erp-table-actions-col">
                        @if ($job)
                            <x-admin.table-row-actions>
                                <x-admin.table-row-action :href="route('admin.production.job-cards.show', $job)">
                                    {{ __('View job') }}
                                </x-admin.table-row-action>
                            </x-admin.table-row-actions>
                        @else
                            —
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-admin.empty-state
                            icon="switch-horizontal"
                            :title="__('No active queue entries')"
                            :description="__('Jobs appear here when queued to this work center.')"
                        />
                    </td>
                </tr>
            @endforelse
        </x-slot:body>
    </x-admin.data-table>
</x-admin-layout>
