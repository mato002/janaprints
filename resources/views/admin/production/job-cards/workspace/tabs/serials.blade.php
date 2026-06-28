@php
    $allocation = $tabData['allocation'] ?? null;
    $loss = $tabData['loss_metrics'] ?? [];
@endphp

@if ($allocation)
    <div class="mb-6 rounded-xl border-2 border-erp-primary bg-erp-primary/5 p-6">
        <h3 class="text-lg font-bold text-erp-primary">{{ __('Serial Allocation') }}</h3>
        <p class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-slate-900">
            {{ $allocation->formatSerial($allocation->serial_start) }}
            <span class="text-slate-400">—</span>
            {{ $allocation->formatSerial($allocation->serial_end) }}
        </p>
        <p class="mt-1 text-sm text-slate-600">{{ __('Quantity') }}: {{ $allocation->allocatedQuantity() }}</p>
        @if ($allocation->is_confirmed)
            <p class="mt-2 text-sm text-emerald-700">
                {{ __('Confirmed') }} {{ $allocation->confirmed_at?->format('Y-m-d H:i') }}
                @if ($allocation->confirmedByUser) — {{ $allocation->confirmedByUser->name }} @endif
            </p>
        @endif
    </div>

    @if (($tabData['can_confirm'] ?? false) && ! $allocation->is_confirmed)
        <x-admin.card class="mb-6">
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Confirm Production') }}</h3>
            <form method="POST" action="{{ route('admin.production.job-cards.serials.confirm', $jobCard) }}" class="grid grid-cols-1 gap-4 md:grid-cols-3 max-w-3xl">
                @csrf
                <div>
                    <label class="erp-label">{{ __('Produced (last serial number)') }}</label>
                    <input type="number" name="produced_end" class="erp-input w-full" min="{{ $allocation->serial_start }}" max="{{ $allocation->serial_end }}" required>
                </div>
                <div>
                    <label class="erp-label">{{ __('Spoiled start') }}</label>
                    <input type="number" name="spoiled_start" class="erp-input w-full" min="{{ $allocation->serial_start }}" max="{{ $allocation->serial_end }}">
                </div>
                <div>
                    <label class="erp-label">{{ __('Spoiled end') }}</label>
                    <input type="number" name="spoiled_end" class="erp-input w-full" min="{{ $allocation->serial_start }}" max="{{ $allocation->serial_end }}">
                </div>
                <div class="md:col-span-3">
                    <button type="submit" class="erp-btn-primary">{{ __('Confirm serial production') }}</button>
                </div>
            </form>
        </x-admin.card>
    @endif

    @if ($allocation->is_confirmed)
        <x-admin.card class="mb-6">
            <dl class="grid grid-cols-1 gap-4 text-sm md:grid-cols-3">
                <div>
                    <dt class="text-slate-500">{{ __('Produced through') }}</dt>
                    <dd class="font-medium tabular-nums">{{ $allocation->produced_end ? $allocation->formatSerial($allocation->produced_end) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Spoiled quantity') }}</dt>
                    <dd class="font-medium text-red-700">{{ $allocation->spoiled_quantity }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">{{ __('Production loss (auditable)') }}</dt>
                    <dd class="font-medium">{{ $loss['production_loss_quantity'] ?? 0 }}</dd>
                </div>
            </dl>
        </x-admin.card>
    @endif

    @if (($tabData['spoiled_ranges'] ?? collect())->isNotEmpty())
        <x-admin.card>
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Spoiled Serial Ranges') }}</h3>
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Range') }}</th>
                        <th>{{ __('Quantity') }}</th>
                        <th>{{ __('Recorded') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($tabData['spoiled_ranges'] as $range)
                        <tr>
                            <td class="tabular-nums">{{ $range->serial_start }} – {{ $range->serial_end }}</td>
                            <td>{{ $range->quantity }}</td>
                            <td>{{ $range->recorded_at?->format('Y-m-d H:i') }} @if($range->recordedByUser) ({{ $range->recordedByUser->name }}) @endif</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </x-admin.card>
    @endif
@else
    <x-admin.empty-state :title="__('No serial allocation')" :description="__('This job card product does not use serial numbers.')" />
@endif
