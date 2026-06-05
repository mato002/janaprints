<x-admin-layout
    :title="$asset->asset_name"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Machines'), 'url' => route('admin.assets.machines.index')],
        ['label' => $asset->asset_name],
    ]"
>
    <x-admin.page-header :title="$asset->asset_name" :description="$profile->machine_code">
        <x-slot name="actions">
            <x-admin.status-badge :variant="$profile->production_status->badgeVariant()">{{ $profile->production_status->label() }}</x-admin.status-badge>
            <a href="{{ route('admin.assets.show', $asset) }}" class="erp-btn-secondary">{{ __('View Asset') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="mb-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        <x-admin.kpi-widget :label="__('Utilization')" :value="($capacity['current_utilization'] ?? 0).'%'" icon="chart-pie" />
        <x-admin.kpi-widget :label="__('Jobs Assigned')" :value="$queue_readiness['jobs_assigned']" icon="clipboard-list" />
        <x-admin.kpi-widget :label="__('Capacity Remaining')" :value="$queue_readiness['capacity_remaining']" icon="cog" />
        <x-admin.kpi-widget :label="__('Availability')" :value="$availability['label']" icon="check-circle" />
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Overview') }}</h3>
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">{{ __('Machine Code') }}</dt><dd class="font-mono">{{ $profile->machine_code }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ $profile->machine_type }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Manufacturer') }}</dt><dd>{{ $profile->manufacturer ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Model') }}</dt><dd>{{ $profile->model ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Serial Number') }}</dt><dd>{{ $profile->serial_number ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Production Area') }}</dt><dd>{{ $profile->production_area ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Installation Date') }}</dt><dd>{{ $profile->installation_date?->format('Y-m-d') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Primary Machine') }}</dt><dd>{{ $profile->is_primary_production_machine ? __('Yes') : __('No') }}</dd></div>
                </dl>
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Capacity') }}</h3>
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">{{ __('Capacity Unit') }}</dt><dd>{{ $profile->capacity_unit?->value ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Hourly Capacity') }}</dt><dd>{{ number_format($capacity['hourly_capacity'] ?? 0, 2) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Shift Capacity') }}</dt><dd>{{ number_format($capacity['shift_capacity'] ?? 0, 2) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Daily Capacity') }}</dt><dd>{{ number_format($capacity['daily_capacity'] ?? 0, 2) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Monthly Capacity') }}</dt><dd>{{ number_format($capacity['monthly_capacity'] ?? 0, 2) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Expected Throughput / hr') }}</dt><dd>{{ number_format($profile->capacity_per_hour ?: $profile->hourly_capacity, 2) }}</dd></div>
                </dl>
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Work Center') }}</h3>
                @if ($profile->workCenter)
                    <p class="text-sm font-medium">{{ $profile->workCenter->name }}</p>
                    <p class="text-xs text-slate-500">{{ $profile->workCenter->code }}</p>
                @else
                    <p class="text-sm text-slate-500">{{ __('Not assigned to a work center.') }}</p>
                @endif
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Assigned Jobs') }}</h3>
                @if ($assigned_jobs->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('No active job assignments.') }}</p>
                @else
                    <ul class="divide-y divide-erp-border text-sm">
                        @foreach ($assigned_jobs as $job)
                            <li class="flex justify-between gap-2 py-2">
                                <a href="{{ route('admin.production.job-cards.show', $job) }}" class="erp-link font-mono">{{ $job->job_card_number }}</a>
                                <span class="text-slate-500">{{ str_replace('_', ' ', $job->status->value ?? $job->status) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Timeline') }}</h3>
                @if ($timeline->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('No machine activity yet.') }}</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach ($timeline as $entry)
                            <li class="border-b border-erp-border pb-2">
                                <p class="font-medium">{{ $entry->title }}</p>
                                @if ($entry->description)
                                    <p class="text-slate-600">{{ $entry->description }}</p>
                                @endif
                                <p class="text-xs text-slate-500">{{ $entry->user?->name }} — {{ $entry->occurred_at?->format('Y-m-d H:i') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Documents') }}</h3>
                <p class="text-sm text-slate-500">{{ __('Manuals, certificates, installation reports, and technical documents will be available in a later phase.') }}</p>
            </x-admin.card>
        </div>

        <div class="space-y-4">
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Actions') }}</h3>
                <div class="flex flex-col gap-2">
                    @can('assign', $profile)
                        <details class="rounded border border-erp-border p-3">
                            <summary class="cursor-pointer text-sm font-medium">{{ __('Assign Work Center') }}</summary>
                            <form method="POST" action="{{ route('admin.assets.machines.work-center', $asset) }}" class="mt-3 space-y-2">
                                @csrf
                                <select name="work_center_id" class="erp-select w-full">
                                    <option value="">{{ __('Unassigned') }}</option>
                                    @foreach ($work_centers as $center)
                                        <option value="{{ $center->id }}" @selected($profile->workCenter?->id === $center->id)>{{ $center->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="erp-btn-primary w-full">{{ __('Save') }}</button>
                            </form>
                        </details>
                    @endcan

                    @can('manage', $profile)
                        <details class="rounded border border-erp-border p-3">
                            <summary class="cursor-pointer text-sm font-medium">{{ __('Change Status') }}</summary>
                            <form method="POST" action="{{ route('admin.assets.machines.status', $asset) }}" class="mt-3 space-y-2">
                                @csrf
                                <select name="production_status" class="erp-select w-full" required>
                                    @foreach (\App\Enums\ProductionMachineStatus::cases() as $status)
                                        <option value="{{ $status->value }}" @selected($profile->production_status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="erp-btn-secondary w-full">{{ __('Update Status') }}</button>
                            </form>
                        </details>
                    @endcan

                    @can('updateCapacity', $profile)
                        <details class="rounded border border-erp-border p-3">
                            <summary class="cursor-pointer text-sm font-medium">{{ __('Update Capacity') }}</summary>
                            <form method="POST" action="{{ route('admin.assets.machines.capacity', $asset) }}" class="mt-3 space-y-2">
                                @csrf
                                <input type="number" step="0.01" min="0" name="hourly_capacity" value="{{ $profile->hourly_capacity }}" class="erp-input w-full" placeholder="{{ __('Hourly capacity') }}">
                                <input type="number" step="0.01" min="0" name="shift_capacity" value="{{ $profile->shift_capacity }}" class="erp-input w-full" placeholder="{{ __('Shift capacity') }}">
                                <input type="number" step="0.01" min="0" name="daily_capacity" value="{{ $profile->daily_capacity }}" class="erp-input w-full" placeholder="{{ __('Daily capacity') }}">
                                <input type="number" step="0.01" min="0" name="monthly_capacity" value="{{ $profile->monthly_capacity }}" class="erp-input w-full" placeholder="{{ __('Monthly capacity') }}">
                                <button type="submit" class="erp-btn-secondary w-full">{{ __('Save Capacity') }}</button>
                            </form>
                        </details>
                    @endcan

                    <a href="{{ route('admin.assets.show', $asset) }}" class="erp-btn-secondary w-full text-center">{{ __('View Asset') }}</a>
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin-layout>
