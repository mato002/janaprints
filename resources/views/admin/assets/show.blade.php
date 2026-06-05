<x-admin-layout
    :title="$asset->asset_number"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Register'), 'url' => route('admin.assets.index')],
        ['label' => $asset->asset_number],
    ]"
>
    <x-admin.page-header :title="$asset->asset_name" :description="$asset->asset_number">
        <x-slot name="actions">
            <x-admin.status-badge :variant="$asset->status->badgeVariant()">{{ $asset->status->label() }}</x-admin.status-badge>
            <a href="{{ route('admin.assets.barcode', $asset) }}" class="erp-btn-secondary" target="_blank">{{ __('Print Barcode') }}</a>
            @if ($asset->machineProfile)
                @can('view', $asset->machineProfile)
                    <a href="{{ route('admin.assets.machines.show', $asset) }}" class="erp-btn-secondary">{{ __('Machine Profile') }}</a>
                @endcan
            @endif
            @can('view360', $asset)
                <a href="{{ route('admin.assets.360.show', $asset) }}" class="erp-btn-primary">{{ __('View 360') }}</a>
            @endcan
            @can('view', $asset)
                @can('viewAny', \App\Models\Assets\DepreciationRun::class)
                    <a href="{{ route('admin.assets.finance.profile', $asset) }}" class="erp-btn-secondary">{{ __('Financial Profile') }}</a>
                @endcan
            @endcan
            @can('update', $asset)
                <a href="{{ route('admin.assets.edit', $asset) }}" class="erp-btn-secondary">{{ __('Edit Asset') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Overview') }}</h3>
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">{{ __('Category') }}</dt><dd>{{ $asset->category?->name }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Branch') }}</dt><dd>{{ $asset->branch?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Serial Number') }}</dt><dd>{{ $asset->serial_number ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Barcode') }}</dt><dd>{{ $asset->barcode ?? $asset->asset_number }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Manufacturer') }}</dt><dd>{{ $asset->manufacturer ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Model') }}</dt><dd>{{ $asset->model ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Notes') }}</dt><dd>{{ $asset->notes ?: '—' }}</dd></div>
                </dl>
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Financial Information') }}</h3>
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">{{ __('Acquisition Date') }}</dt><dd>{{ $asset->acquisition_date?->format('Y-m-d') }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Acquisition Cost') }}</dt><dd>{{ number_format($asset->acquisition_cost, 2) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Residual Value') }}</dt><dd>{{ number_format($asset->residual_value, 2) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Accumulated Depreciation') }}</dt><dd>{{ number_format($asset->accumulated_depreciation, 2) }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Book Value') }}</dt><dd class="font-semibold">{{ number_format($asset->netBookValue(), 2) }}</dd></div>
                </dl>
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Custody & Assignment') }}</h3>
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">{{ __('Custody Status') }}</dt><dd>{{ $asset->custody_status?->label() ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Current Condition') }}</dt><dd>{{ $asset->current_condition?->label() ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Assigned Employee') }}</dt><dd>{{ $asset->assignedEmployee?->full_name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Assigned Department') }}</dt><dd>{{ $asset->assignedDepartment?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Assigned User') }}</dt><dd>{{ $asset->assignedUser?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Assigned Branch') }}</dt><dd>{{ $asset->assignedBranch?->name ?? '—' }}</dd></div>
                </dl>
                @if ($asset->assignmentHistories->isNotEmpty())
                    <div class="mt-4 overflow-x-auto">
                        <table class="erp-table w-full text-sm">
                            <thead><tr><th>{{ __('Type') }}</th><th>{{ __('Assigned To') }}</th><th>{{ __('Status') }}</th><th>{{ __('By') }}</th><th>{{ __('Date') }}</th></tr></thead>
                            <tbody>
                                @foreach ($asset->assignmentHistories as $history)
                                    <tr>
                                        <td>{{ ucfirst($history->assignment_type->value) }}</td>
                                        <td>{{ $history->assignedEmployee?->full_name ?? $history->assignedDepartment?->name ?? $history->assignedUser?->name ?? $history->assignedBranch?->name ?? '—' }}</td>
                                        <td>{{ $history->status?->label() ?? '—' }}</td>
                                        <td>{{ $history->assigner?->name }}</td>
                                        <td>{{ $history->assigned_at?->format('Y-m-d H:i') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.card>

            @if ($asset->custodyTimelineEntries->isNotEmpty())
                <x-admin.card>
                    <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Custody Timeline') }}</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($asset->custodyTimelineEntries as $entry)
                            <li class="flex justify-between gap-3 border-b border-erp-border pb-2">
                                <span><span class="font-medium">{{ $entry->title }}</span> — {{ $entry->user?->name ?? __('System') }}</span>
                                <span class="text-slate-500">{{ $entry->occurred_at?->format('Y-m-d H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </x-admin.card>
            @endif

            @if (! $asset->machineProfile)
                @can('create', \App\Models\Assets\MachineProfile::class)
                    <x-admin.card>
                        <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Production Machine') }}</h3>
                        <p class="mb-3 text-sm text-slate-500">{{ __('Activate this asset as a production machine.') }}</p>
                        <form method="POST" action="{{ route('admin.assets.machines.activate', $asset) }}" class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            @csrf
                            <div>
                                <label class="erp-label">{{ __('Machine Code') }}</label>
                                <input type="text" name="machine_code" class="erp-input w-full" required maxlength="50" value="{{ strtoupper(substr($asset->asset_number, -8)) }}">
                            </div>
                            <div>
                                <label class="erp-label">{{ __('Machine Type') }}</label>
                                <input type="text" name="machine_type" class="erp-input w-full" required maxlength="50" placeholder="{{ __('Offset Press, Digital Press…') }}">
                            </div>
                            <div>
                                <label class="erp-label">{{ __('Shift Capacity') }}</label>
                                <input type="number" step="0.01" min="0" name="shift_capacity" class="erp-input w-full" value="10">
                            </div>
                            <div>
                                <label class="erp-label">{{ __('Hourly Capacity') }}</label>
                                <input type="number" step="0.01" min="0" name="hourly_capacity" class="erp-input w-full" value="2">
                            </div>
                            <div class="sm:col-span-2">
                                <button type="submit" class="erp-btn-primary">{{ __('Activate for Production') }}</button>
                            </div>
                        </form>
                    </x-admin.card>
                @endcan
            @endif

            <x-admin.card>
                <div class="mb-3 flex items-center justify-between gap-2">
                    <h3 class="text-sm font-semibold text-slate-900">{{ __('Maintenance Timeline') }}</h3>
                    @can('maintenance.view')
                        @if (Route::has('admin.assets.maintenance.work-orders.index'))
                            <a href="{{ route('admin.assets.maintenance.work-orders.index', ['search' => $asset->asset_number]) }}" class="text-xs text-erp-accent hover:underline">{{ __('Work Orders') }}</a>
                        @endif
                    @endcan
                </div>
                @if ($asset->maintenanceTimelineEntries->isEmpty() && $asset->maintenanceWorkOrders->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('No maintenance activity recorded yet.') }}</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach ($asset->maintenanceTimelineEntries as $entry)
                            <li class="border-b border-erp-border pb-2">
                                <p class="font-medium">{{ $entry->title }}</p>
                                <p class="text-xs text-slate-500">{{ $entry->user?->name }} — {{ $entry->occurred_at?->format('Y-m-d H:i') }}</p>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Documents') }}</h3>
                <p class="text-sm text-slate-500">{{ __('Document storage will be available in a later phase.') }}</p>
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Audit History') }}</h3>
                @if ($activityLogs->isEmpty())
                    <p class="text-sm text-slate-500">{{ __('No activity recorded yet.') }}</p>
                @else
                    <ul class="space-y-2 text-sm">
                        @foreach ($activityLogs as $log)
                            <li class="flex justify-between gap-3 border-b border-erp-border pb-2">
                                <span>{{ ucfirst(str_replace('_', ' ', $log->action)) }} — {{ $log->user?->name ?? __('System') }}</span>
                                <span class="text-slate-500">{{ $log->created_at?->format('Y-m-d H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-admin.card>
        </div>

        <div class="space-y-4">
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold text-slate-900">{{ __('Actions') }}</h3>
                <div class="flex flex-col gap-2">
                    @can('assign', $asset)
                        <details class="rounded border border-erp-border p-3">
                            <summary class="cursor-pointer text-sm font-medium">{{ __('Assign Asset') }}</summary>
                            <form method="POST" action="{{ route('admin.assets.assign', $asset) }}" class="mt-3 space-y-2">
                                @csrf
                                <select name="assignment_type" class="erp-select w-full" required>
                                    <option value="user">{{ __('User') }}</option>
                                    <option value="branch">{{ __('Branch') }}</option>
                                </select>
                                <select name="assigned_to_user_id" class="erp-select w-full">
                                    <option value="">{{ __('Select user…') }}</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                                <select name="assigned_to_branch_id" class="erp-select w-full">
                                    <option value="">{{ __('Select branch…') }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="erp-btn-primary w-full">{{ __('Assign') }}</button>
                            </form>
                        </details>
                        <a href="{{ route('admin.assets.transfer', $asset) }}" class="erp-btn-secondary w-full text-center">{{ __('Transfer Asset') }}</a>
                        <details class="rounded border border-erp-border p-3">
                            <summary class="cursor-pointer text-sm font-medium">{{ __('Change Status') }}</summary>
                            <form method="POST" action="{{ route('admin.assets.status', $asset) }}" class="mt-3 space-y-2">
                                @csrf
                                <select name="status" class="erp-select w-full" required>
                                    @foreach ($statuses as $status)
                                        <option value="{{ $status->value }}" @selected($asset->status === $status)>{{ $status->label() }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="erp-btn-secondary w-full">{{ __('Update Status') }}</button>
                            </form>
                        </details>
                        <form method="POST" action="{{ route('admin.assets.archive', $asset) }}" onsubmit="return confirm('{{ __('Archive this asset?') }}')">
                            @csrf
                            <button type="submit" class="erp-btn-secondary w-full">{{ __('Archive Asset') }}</button>
                        </form>
                    @endcan
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin-layout>
