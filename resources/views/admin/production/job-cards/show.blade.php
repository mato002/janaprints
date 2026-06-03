<x-admin-layout :title="$jobCard->job_card_number" :breadcrumbs="[['label' => __('Production'), 'url' => route('admin.production.dashboard')], ['label' => $jobCard->job_card_number]]">
    <x-admin.page-header :title="$jobCard->job_card_number" :description="$jobCard->customer?->company_name">
        <span class="erp-badge">{{ str_replace('_', ' ', $jobCard->status->value) }}</span>
        @if ($jobCard->isDelayed())<span class="text-sm text-red-600">{{ __('Delayed') }}</span>@endif
        @can('update', $jobCard)
            <a href="{{ route('admin.production.job-cards.edit', $jobCard) }}" class="erp-btn-secondary">{{ __('Edit') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.card class="mb-6">
        <h3 class="font-medium mb-3">{{ __('Workflow') }}</h3>
        <div class="flex flex-wrap gap-2">
            @can('schedule', $jobCard)
                @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::Queued))
                    <form method="POST" action="{{ route('admin.production.job-cards.queue', $jobCard) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Queue') }}</button></form>
                @endif
            @endcan
            @can('start', $jobCard)
                @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::InProduction))
                    <form method="POST" action="{{ route('admin.production.job-cards.start', $jobCard) }}">@csrf
                        <button class="erp-btn-primary">{{ __('Start production') }}</button></form>
                @endif
            @endcan
            @can('complete', $jobCard)
                @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::QualityCheck))
                    <form method="POST" action="{{ route('admin.production.job-cards.send-to-qc', $jobCard) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('Send to QC') }}</button></form>
                @endif
                @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::ReadyForDispatch))
                    <form method="POST" action="{{ route('admin.production.job-cards.ready-for-dispatch', $jobCard) }}">@csrf
                        <button class="erp-btn-primary">{{ __('Ready for dispatch') }}</button></form>
                @endif
            @endcan
            @can('transition', $jobCard)
                @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::OnHold))
                    <form method="POST" action="{{ route('admin.production.job-cards.hold', $jobCard) }}">@csrf
                        <button class="erp-btn-secondary">{{ __('On hold') }}</button></form>
                @endif
                @if ($jobCard->status->canTransitionTo(App\Enums\ProductionJobCardStatus::Cancelled))
                    <form method="POST" action="{{ route('admin.production.job-cards.cancel', $jobCard) }}">@csrf
                        <button class="erp-btn-secondary text-red-600">{{ __('Cancel') }}</button></form>
                @endif
            @endcan
        </div>
    </x-admin.card>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Traceability') }}</h3>
            <dl class="text-sm space-y-2">
                <div><dt class="text-slate-500">{{ __('Customer') }}</dt><dd>{{ $jobCard->customer?->company_name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Quotation') }}</dt><dd>{{ $jobCard->quotation?->quotation_number }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Artwork') }}</dt><dd>{{ $jobCard->artworkRequest?->request_number }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Sales order') }}</dt><dd>{{ $jobCard->salesOrder?->order_number }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Schedule') }}</h3>
            <dl class="text-sm space-y-2">
                <div><dt class="text-slate-500">{{ __('Planned') }}</dt><dd>{{ $jobCard->planned_start_date?->format('Y-m-d') ?? '—' }} → {{ $jobCard->planned_end_date?->format('Y-m-d') ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Actual') }}</dt><dd>{{ $jobCard->actual_start_date?->format('Y-m-d H:i') ?? '—' }} → {{ $jobCard->actual_end_date?->format('Y-m-d H:i') ?? '—' }}</dd></div>
            </dl>
            @can('schedule', $jobCard)
                <form method="POST" action="{{ route('admin.production.job-cards.schedule', $jobCard) }}" class="mt-4 flex flex-wrap gap-2">
                    @csrf
                    <input type="date" name="planned_start_date" class="erp-input" value="{{ $jobCard->planned_start_date?->format('Y-m-d') }}" required>
                    <input type="date" name="planned_end_date" class="erp-input" value="{{ $jobCard->planned_end_date?->format('Y-m-d') }}" required>
                    <button class="erp-btn-secondary">{{ __('Update schedule') }}</button>
                </form>
            @endcan
        </x-admin.card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Production queue') }}</h3>
            @foreach ($jobCard->queues as $entry)
                <div class="text-sm py-1">{{ $entry->workCenter?->name }} — #{{ $entry->queue_position }} ({{ $entry->status->value }})</div>
            @endforeach
            @can('create', [App\Models\Production\ProductionQueue::class, $jobCard])
                <form method="POST" action="{{ route('admin.production.queues.store', $jobCard) }}" class="mt-4 space-y-2">
                    @csrf
                    <select name="work_center_id" class="erp-input w-full" required>
                        @foreach (\App\Models\Production\WorkCenter::query()->forTenant()->where('is_active', true)->orderBy('name')->get() as $wc)
                            <option value="{{ $wc->id }}">{{ $wc->name }}</option>
                        @endforeach
                    </select>
                    <input type="number" name="queue_position" class="erp-input w-full" value="1" min="1" required>
                    <button class="erp-btn-secondary">{{ __('Add to queue') }}</button>
                </form>
            @endcan
        </x-admin.card>

        <x-admin.card>
            <h3 class="font-medium mb-3">{{ __('Quality control') }}</h3>
            @foreach ($jobCard->qualityChecks as $check)
                <div class="text-sm py-1">{{ $check->result->value }} — {{ $check->checker?->name }} ({{ $check->checked_at?->format('Y-m-d H:i') }})</div>
            @endforeach
            @can('create', [App\Models\Production\QualityCheck::class, $jobCard])
                <form method="POST" action="{{ route('admin.production.quality-checks.store', $jobCard) }}" class="mt-4 space-y-2">
                    @csrf
                    <select name="result" class="erp-input w-full" required>
                        <option value="passed">{{ __('Passed') }}</option>
                        <option value="failed">{{ __('Failed') }}</option>
                        <option value="rework_required">{{ __('Rework required') }}</option>
                    </select>
                    <textarea name="comments" class="erp-input w-full" rows="2" placeholder="{{ __('Comments') }}"></textarea>
                    <button class="erp-btn-primary">{{ __('Record QC') }}</button>
                </form>
            @endcan
        </x-admin.card>
    </div>

    <x-admin.card class="mt-6">
        <h3 class="font-medium mb-3">{{ __('Material consumption') }}</h3>
        @foreach ($jobCard->materialConsumptions as $consumption)
            <div class="text-sm py-1">{{ $consumption->inventoryItem?->item_name }}: {{ $consumption->quantity }} ({{ $consumption->warehouse?->name }})</div>
        @endforeach
        @can('inventory.issue', auth()->user())
            <form method="POST" action="{{ route('admin.inventory.production.consume', $jobCard) }}" class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-2">
                @csrf
                <select name="inventory_item_id" class="erp-input" required>
                    @foreach (\App\Models\Inventory\InventoryItem::query()->forTenant()->where('is_active', true)->get() as $inv)
                        <option value="{{ $inv->id }}">{{ $inv->sku }}</option>
                    @endforeach
                </select>
                <select name="warehouse_id" class="erp-input" required>
                    @foreach (\App\Models\Inventory\Warehouse::query()->forTenant()->where('is_active', true)->get() as $wh)
                        <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
                <input type="number" step="0.001" name="quantity" class="erp-input" placeholder="{{ __('Qty') }}" required>
                <button class="erp-btn-secondary md:col-span-3">{{ __('Record consumption') }}</button>
            </form>
        @endcan
    </x-admin.card>

    <x-admin.card class="mt-6">
        <h3 class="font-medium mb-3">{{ __('Operations') }}</h3>
        @foreach ($jobCard->operations as $op)
            <div class="text-sm border-b py-2 flex justify-between">
                <span>{{ $op->workCenter?->name }} / {{ $op->stage?->name }}</span>
                <span>{{ $op->started_at?->format('Y-m-d H:i') }} — {{ $op->ended_at?->format('Y-m-d H:i') ?? __('ongoing') }}</span>
            </div>
        @endforeach
        @can('start', $jobCard)
            <form method="POST" action="{{ route('admin.production.operations.store', $jobCard) }}" class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-2">
                @csrf
                <select name="work_center_id" class="erp-input" required>
                    @foreach (\App\Models\Production\WorkCenter::query()->forTenant()->where('is_active', true)->get() as $wc)
                        <option value="{{ $wc->id }}">{{ $wc->name }}</option>
                    @endforeach
                </select>
                <select name="production_stage_id" class="erp-input" required>
                    @foreach (\App\Models\Production\ProductionStage::query()->forTenant()->where('is_active', true)->orderBy('sort_order')->get() as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
                <button class="erp-btn-secondary md:col-span-2">{{ __('Log operation') }}</button>
            </form>
        @endcan
    </x-admin.card>
</x-admin-layout>
