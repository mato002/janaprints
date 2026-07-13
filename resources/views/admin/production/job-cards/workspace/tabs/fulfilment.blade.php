@php
    $fulfilment = $tabData['fulfilment'] ?? null;
    $method = $tabData['fulfilment_method'] ?? null;
    $ready = $tabData['ready_for_dispatch'] ?? false;
    $canFulfil = $tabData['can_fulfil'] ?? false;
@endphp

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
    <x-admin.card>
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Fulfilment method') }}</h3>
        <p class="text-lg font-medium">{{ $method?->label() ?? __('Collection') }}</p>
        <p class="mt-2 text-sm text-slate-600">{{ __('From sales order') }}</p>
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Status') }}</h3>
        <p class="text-lg font-medium">{{ $fulfilment?->status?->label() ?? __('Pending') }}</p>
        @if ($tabData['invoice_ready'] ?? false)
            <span class="erp-badge mt-2 bg-emerald-100 text-emerald-800">{{ __('Ready for invoice') }}</span>
        @endif
    </x-admin.card>

    <x-admin.card>
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Job readiness') }}</h3>
        <p class="text-sm {{ $ready ? 'text-emerald-700' : 'text-amber-700' }}">
            {{ $ready ? __('Ready for dispatch') : __('Job not yet ready for fulfilment') }}
        </p>
    </x-admin.card>
</div>

@if ($method?->value === 'collection')
    <x-admin.card class="mb-6">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Collection') }}</h3>

        @if ($fulfilment?->prepared_at)
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500">{{ __('Prepared by') }}</dt><dd>{{ $fulfilment->preparedByUser?->name ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Prepared at') }}</dt><dd>{{ $fulfilment->prepared_at->format('M j, Y H:i') }}</dd></div>
                @if ($fulfilment->collection_notes)
                    <div class="md:col-span-2"><dt class="text-slate-500">{{ __('Collection notes') }}</dt><dd>{{ $fulfilment->collection_notes }}</dd></div>
                @endif
            </dl>
        @endif

        @if ($fulfilment?->collected_at)
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-2 border-t border-erp-border pt-4">
                <div><dt class="text-slate-500">{{ __('Collected by') }}</dt><dd>{{ $fulfilment->collected_by_name }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Collected at') }}</dt><dd>{{ $fulfilment->collected_at->format('M j, Y H:i') }}</dd></div>
                @if ($fulfilment->collector_id_number)
                    <div><dt class="text-slate-500">{{ __('ID number') }}</dt><dd>{{ $fulfilment->collector_id_number }}</dd></div>
                @endif
                @if ($fulfilment->collector_phone)
                    <div><dt class="text-slate-500">{{ __('Phone') }}</dt><dd>{{ $fulfilment->collector_phone }}</dd></div>
                @endif
                @if ($fulfilment->collection_remarks)
                    <div class="md:col-span-2"><dt class="text-slate-500">{{ __('Remarks') }}</dt><dd>{{ $fulfilment->collection_remarks }}</dd></div>
                @endif
            </dl>
        @endif

        @if ($canFulfil && $ready)
            @if ($fulfilment?->status?->value === 'pending')
                <form method="POST" action="{{ route('admin.production.job-cards.fulfilment.ready-for-collection', $jobCard) }}" class="space-y-3 max-w-lg">
                    @csrf
                    <div>
                        <label class="erp-label">{{ __('Collection notes') }}</label>
                        <textarea name="collection_notes" class="erp-input w-full" rows="2"></textarea>
                    </div>
                    <x-primary-button type="submit">{{ __('Mark ready for collection') }}</x-primary-button>
                </form>
            @elseif ($fulfilment?->status?->value === 'ready_for_collection')
                <form method="POST" action="{{ route('admin.production.job-cards.fulfilment.confirm-collection', [$jobCard, $fulfilment]) }}" class="space-y-3 max-w-lg">
                    @csrf
                    <div>
                        <label class="erp-label">{{ __('Collected by') }}</label>
                        <input type="text" name="collected_by_name" class="erp-input w-full" required>
                    </div>
                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="erp-label">{{ __('ID number') }}</label>
                            <input type="text" name="collector_id_number" class="erp-input w-full">
                        </div>
                        <div>
                            <label class="erp-label">{{ __('Phone') }}</label>
                            <input type="text" name="collector_phone" class="erp-input w-full">
                        </div>
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Remarks') }}</label>
                        <textarea name="collection_remarks" class="erp-input w-full" rows="2"></textarea>
                    </div>
                    <x-primary-button type="submit">{{ __('Confirm collection') }}</x-primary-button>
                </form>
            @endif
        @endif
    </x-admin.card>
@else
    <x-admin.card class="mb-6">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Delivery') }}</h3>

        @if ($fulfilment?->dispatched_at || $fulfilment?->recipient_name)
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-2">
                <div><dt class="text-slate-500">{{ __('Recipient') }}</dt><dd>{{ $fulfilment->recipient_name ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Phone') }}</dt><dd>{{ $fulfilment->recipient_phone ?? '—' }}</dd></div>
                <div class="md:col-span-2"><dt class="text-slate-500">{{ __('Address') }}</dt><dd>{{ $fulfilment->delivery_address ?? '—' }}</dd></div>
                @if ($fulfilment->dispatched_at)
                    <div><dt class="text-slate-500">{{ __('Dispatched by') }}</dt><dd>{{ $fulfilment->dispatchedByUser?->name ?? '—' }}</dd></div>
                    <div><dt class="text-slate-500">{{ __('Dispatch date') }}</dt><dd>{{ $fulfilment->dispatch_date?->format('M j, Y') ?? $fulfilment->dispatched_at->format('M j, Y') }}</dd></div>
                @endif
                @if ($fulfilment->deliveryNote)
                    <div class="md:col-span-2">
                        <dt class="text-slate-500">{{ __('Delivery note') }}</dt>
                        <dd><a href="{{ route('admin.dispatch.delivery-notes.show', $fulfilment->deliveryNote) }}" class="font-mono text-indigo-600">{{ $fulfilment->deliveryNote->delivery_note_number }}</a></dd>
                    </div>
                @endif
            </dl>
        @endif

        @if ($fulfilment?->delivered_at)
            <dl class="mb-4 grid grid-cols-1 gap-2 text-sm md:grid-cols-2 border-t border-erp-border pt-4">
                <div><dt class="text-slate-500">{{ __('Received by') }}</dt><dd>{{ $fulfilment->received_by ?? '—' }}</dd></div>
                <div><dt class="text-slate-500">{{ __('Delivered at') }}</dt><dd>{{ $fulfilment->delivered_at->format('M j, Y H:i') }}</dd></div>
                @if ($fulfilment->signature_name)
                    <div><dt class="text-slate-500">{{ __('Signature name') }}</dt><dd>{{ $fulfilment->signature_name }}</dd></div>
                @endif
                @if ($fulfilment->delivery_remarks)
                    <div class="md:col-span-2"><dt class="text-slate-500">{{ __('Remarks') }}</dt><dd>{{ $fulfilment->delivery_remarks }}</dd></div>
                @endif
            </dl>
        @endif

        @if ($canFulfil && $ready && ! in_array($fulfilment?->status?->value, ['delivered', 'collected'], true))
            @if (in_array($fulfilment?->status?->value, ['pending', null], true))
                @if ($fulfilment && $fulfilment->recipient_name)
                    <form method="POST" action="{{ route('admin.production.job-cards.fulfilment.prepare-delivery', [$jobCard, $fulfilment]) }}" class="space-y-3 max-w-lg mb-4">
                        @csrf
                        <p class="text-xs text-slate-500">{{ __('Update saved delivery details before dispatch.') }}</p>
                        <div>
                            <label class="erp-label">{{ __('Recipient name') }}</label>
                            <input type="text" name="recipient_name" class="erp-input w-full" value="{{ old('recipient_name', $fulfilment->recipient_name) }}" required>
                        </div>
                        <div>
                            <label class="erp-label">{{ __('Recipient phone') }}</label>
                            <input type="text" name="recipient_phone" class="erp-input w-full" value="{{ old('recipient_phone', $fulfilment->recipient_phone) }}">
                        </div>
                        <div>
                            <label class="erp-label">{{ __('Delivery address') }}</label>
                            <textarea name="delivery_address" class="erp-input w-full" rows="3" required>{{ old('delivery_address', $fulfilment->delivery_address) }}</textarea>
                        </div>
                        <div>
                            <label class="erp-label">{{ __('Dispatch date') }}</label>
                            <input type="date" name="dispatch_date" class="erp-input w-full" value="{{ old('dispatch_date', $fulfilment->dispatch_date?->format('Y-m-d') ?? now()->toDateString()) }}">
                        </div>
                        <x-primary-button type="submit">{{ __('Save delivery details') }}</x-primary-button>
                    </form>
                @endif
                <form method="POST" action="{{ route('admin.production.job-cards.fulfilment.create-delivery', $jobCard) }}" class="space-y-3 max-w-lg">
                    @csrf
                    <div>
                        <label class="erp-label">{{ __('Recipient name') }}</label>
                        <input type="text" name="recipient_name" class="erp-input w-full" required>
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Recipient phone') }}</label>
                        <input type="text" name="recipient_phone" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Delivery address') }}</label>
                        <textarea name="delivery_address" class="erp-input w-full" rows="3" required></textarea>
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Dispatch date') }}</label>
                        <input type="date" name="dispatch_date" class="erp-input w-full" value="{{ now()->toDateString() }}">
                    </div>
                    <x-primary-button type="submit">{{ __('Create delivery & dispatch') }}</x-primary-button>
                </form>
            @elseif ($fulfilment?->status?->value === 'dispatched')
                <form method="POST" action="{{ route('admin.production.job-cards.fulfilment.confirm-delivery', [$jobCard, $fulfilment]) }}" class="space-y-3 max-w-lg">
                    @csrf
                    <div>
                        <label class="erp-label">{{ __('Received by') }}</label>
                        <input type="text" name="received_by" class="erp-input w-full" required>
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Signature name') }} <span class="text-slate-400">({{ __('optional') }})</span></label>
                        <input type="text" name="signature_name" class="erp-input w-full">
                    </div>
                    <div>
                        <label class="erp-label">{{ __('Remarks') }}</label>
                        <textarea name="delivery_remarks" class="erp-input w-full" rows="2"></textarea>
                    </div>
                    <x-primary-button type="submit">{{ __('Confirm delivery') }}</x-primary-button>
                </form>
            @endif
        @endif
    </x-admin.card>
@endif

<p class="text-sm text-slate-500">
    <a href="{{ route('admin.production.job-cards.show', ['jobCard' => $jobCard, 'tab' => 'dispatch']) }}" class="text-indigo-600">{{ __('View dispatch readiness & delivery notes') }}</a>
</p>
