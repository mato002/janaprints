<x-admin-layout :title="$workOrder->work_order_no" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Maintenance'), 'url' => route('admin.assets.maintenance.dashboard', ['tab' => 'work-orders'])], ['label' => $workOrder->work_order_no]]">
    <x-admin.page-header :title="$workOrder->work_order_no" :description="$workOrder->asset?->asset_name">
        <x-slot name="actions">
            <x-admin.status-badge :variant="$workOrder->priority->badgeVariant()">{{ $workOrder->priority->label() }}</x-admin.status-badge>
            <x-admin.status-badge :variant="$workOrder->status->badgeVariant()">{{ $workOrder->status->label() }}</x-admin.status-badge>
            <a href="{{ route('admin.assets.show', $workOrder->asset) }}" class="erp-btn-secondary">{{ __('View Asset') }}</a>
        </x-slot>
    </x-admin.page-header>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="space-y-4 xl:col-span-2">
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold">{{ __('Overview') }}</h3>
                <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                    <div><dt class="text-slate-500">{{ __('Type') }}</dt><dd>{{ $workOrder->maintenance_type->label() }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Opened') }}</dt><dd>{{ $workOrder->opened_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Scheduled') }}</dt><dd>{{ $workOrder->scheduled_for?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Completed') }}</dt><dd>{{ $workOrder->completed_at?->format('Y-m-d H:i') ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Requested By') }}</dt><dd>{{ $workOrder->requester?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Assigned To') }}</dt><dd>{{ $workOrder->assignee?->name ?? $workOrder->technician?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Vendor') }}</dt><dd>{{ $workOrder->vendor?->vendor_name ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Description') }}</dt><dd>{{ $workOrder->description ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Findings') }}</dt><dd>{{ $workOrder->findings ?: '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-slate-500">{{ __('Resolution') }}</dt><dd>{{ $workOrder->resolution ?: '—' }}</dd></div>
                </dl>
            </x-admin.card>

            @if ($workOrder->downtimeRecords->isNotEmpty())
                <x-admin.card>
                    <h3 class="mb-3 text-sm font-semibold">{{ __('Downtime') }}</h3>
                    <ul class="space-y-2 text-sm">
                        @foreach ($workOrder->downtimeRecords as $record)
                            <li>{{ $record->start_time?->format('Y-m-d H:i') }} — {{ $record->duration_minutes }} min ({{ $record->impact_level->label() }})</li>
                        @endforeach
                    </ul>
                </x-admin.card>
            @endif

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold">{{ __('Maintenance Timeline') }}</h3>
                @forelse ($timeline as $entry)
                    <div class="border-b border-erp-border py-2 text-sm">
                        <p class="font-medium">{{ $entry->title }}</p>
                        <p class="text-xs text-slate-500">{{ $entry->user?->name }} — {{ $entry->occurred_at?->format('Y-m-d H:i') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">{{ __('No timeline entries yet.') }}</p>
                @endforelse
            </x-admin.card>

            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold">{{ __('Documents') }}</h3>
                <p class="text-sm text-slate-500">{{ __('Photos, inspection reports, service reports, invoices, and certificates will use the asset document architecture in a later phase.') }}</p>
            </x-admin.card>
        </div>

        <div class="space-y-4">
            <x-admin.card>
                <h3 class="mb-3 text-sm font-semibold">{{ __('Actions') }}</h3>
                <div class="flex flex-col gap-2">
                    @can('manage', $workOrder)
                        @if ($workOrder->status === \App\Enums\MaintenanceWorkOrderStatus::Draft)
                            <form method="POST" action="{{ route('admin.assets.maintenance.work-orders.open', $workOrder) }}">@csrf<button type="submit" class="erp-btn-primary w-full">{{ __('Open Work Order') }}</button></form>
                        @endif
                        @if (in_array($workOrder->status, [\App\Enums\MaintenanceWorkOrderStatus::Open, \App\Enums\MaintenanceWorkOrderStatus::Assigned], true))
                            <form method="POST" action="{{ route('admin.assets.maintenance.work-orders.start', $workOrder) }}">@csrf<button type="submit" class="erp-btn-primary w-full">{{ __('Start Maintenance') }}</button></form>
                        @endif
                    @endcan
                    @can('assign', $workOrder)
                        <details class="rounded border border-erp-border p-3">
                            <summary class="cursor-pointer text-sm font-medium">{{ __('Assign') }}</summary>
                            <form method="POST" action="{{ route('admin.assets.maintenance.work-orders.assign', $workOrder) }}" class="mt-3 space-y-2">@csrf
                                <select name="assigned_to" class="erp-select w-full"><option value="">{{ __('User…') }}</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select>
                                <select name="assigned_technician_id" class="erp-select w-full"><option value="">{{ __('Technician…') }}</option>@foreach ($technicians as $tech)<option value="{{ $tech->id }}">{{ $tech->name }}</option>@endforeach</select>
                                <select name="vendor_id" class="erp-select w-full"><option value="">{{ __('Vendor…') }}</option>@foreach ($vendors as $vendor)<option value="{{ $vendor->id }}">{{ $vendor->vendor_name }}</option>@endforeach</select>
                                <button type="submit" class="erp-btn-secondary w-full">{{ __('Save Assignment') }}</button>
                            </form>
                        </details>
                    @endcan
                    @can('complete', $workOrder)
                        <details class="rounded border border-erp-border p-3">
                            <summary class="cursor-pointer text-sm font-medium">{{ __('Complete') }}</summary>
                            <form method="POST" action="{{ route('admin.assets.maintenance.work-orders.complete', $workOrder) }}" class="mt-3 space-y-2">@csrf
                                <textarea name="findings" class="erp-input w-full" rows="2" placeholder="{{ __('Findings') }}"></textarea>
                                <textarea name="resolution" class="erp-input w-full" rows="2" placeholder="{{ __('Resolution') }}"></textarea>
                                <button type="submit" class="erp-btn-primary w-full">{{ __('Mark Completed') }}</button>
                            </form>
                        </details>
                    @endcan
                    @can('close', $workOrder)
                        @if ($workOrder->status === \App\Enums\MaintenanceWorkOrderStatus::Completed)
                            <form method="POST" action="{{ route('admin.assets.maintenance.work-orders.close', $workOrder) }}">@csrf<button type="submit" class="erp-btn-secondary w-full">{{ __('Close Work Order') }}</button></form>
                        @endif
                    @endcan
                </div>
            </x-admin.card>
        </div>
    </div>
</x-admin-layout>
