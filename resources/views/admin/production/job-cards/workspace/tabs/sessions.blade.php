@php
    $sessions = $tabData['sessions'] ?? null;
    $metrics = $tabData['metrics'] ?? [];
@endphp

<div class="grid grid-cols-2 gap-4 mb-6 md:grid-cols-4">
    <x-admin.card><p class="text-xs text-slate-500">{{ __('Sessions') }}</p><p class="text-2xl font-bold">{{ $metrics['session_count'] ?? 0 }}</p></x-admin.card>
    <x-admin.card><p class="text-xs text-slate-500">{{ __('Total produced') }}</p><p class="text-2xl font-bold">{{ number_format($metrics['total_produced'] ?? 0, 0) }}</p></x-admin.card>
    <x-admin.card><p class="text-xs text-slate-500">{{ __('Total waste') }}</p><p class="text-2xl font-bold text-red-700">{{ number_format($metrics['total_waste'] ?? 0, 0) }}</p></x-admin.card>
    <x-admin.card><p class="text-xs text-slate-500">{{ __('Waste reasons') }}</p><p class="text-sm">{{ count($metrics['waste_by_reason'] ?? []) }}</p></x-admin.card>
</div>

@if ($tabData['can_log'] ?? false)
    <x-admin.card class="mb-6" id="log-session">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Record Production Session') }}</h3>
        <form method="POST" action="{{ route('admin.production.job-cards.sessions.store', $jobCard) }}" class="grid grid-cols-1 gap-3 md:grid-cols-2 lg:grid-cols-3">
            @csrf
            <div><label class="erp-label">{{ __('Start time') }}</label><input type="datetime-local" name="started_at" class="erp-input w-full" required></div>
            <div><label class="erp-label">{{ __('End time') }}</label><input type="datetime-local" name="ended_at" class="erp-input w-full"></div>
            <div><label class="erp-label">{{ __('Expected quantity') }}</label><input type="number" step="0.001" name="expected_quantity" class="erp-input w-full" required></div>
            <div><label class="erp-label">{{ __('Produced quantity') }}</label><input type="number" step="0.001" name="produced_quantity" class="erp-input w-full" required></div>
            <div><label class="erp-label">{{ __('Waste quantity') }}</label><input type="number" step="0.001" name="waste_quantity" class="erp-input w-full" value="0"></div>
            <div>
                <label class="erp-label">{{ __('Waste reason') }}</label>
                <select name="waste_reason" class="erp-input w-full">
                    <option value="">{{ __('—') }}</option>
                    @foreach ($tabData['waste_reasons'] ?? [] as $reason)
                        <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 lg:col-span-3">
                <label class="erp-label">{{ __('Notes') }}</label>
                <textarea name="notes" class="erp-input w-full" rows="2"></textarea>
            </div>
            @if (($tabData['can_capture_materials'] ?? false) && ($tabData['material_requirements'] ?? collect())->isNotEmpty())
                <div class="md:col-span-2 lg:col-span-3">
                    <h4 class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-600">{{ __('Material consumption') }}</h4>
                    <div class="overflow-x-auto">
                        <table class="erp-table w-full text-sm">
                            <thead>
                                <tr>
                                    <th>{{ __('Material') }}</th>
                                    <th>{{ __('Consumed') }}</th>
                                    <th>{{ __('Waste') }}</th>
                                    <th>{{ __('Returned') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($tabData['material_requirements'] as $index => $row)
                                    <tr>
                                        <td>
                                            {{ $row['item_name'] }}
                                            <input type="hidden" name="materials[{{ $index }}][production_material_requirement_id]" value="{{ $row['requirement']->id }}">
                                            <input type="hidden" name="materials[{{ $index }}][inventory_item_id]" value="{{ $row['requirement']->inventory_item_id }}">
                                            <input type="hidden" name="materials[{{ $index }}][warehouse_id]" value="{{ $row['requirement']->warehouse_id }}">
                                        </td>
                                        <td><input type="number" step="0.001" min="0" name="materials[{{ $index }}][consumed_quantity]" class="erp-input w-24 text-sm" value="0"></td>
                                        <td><input type="number" step="0.001" min="0" name="materials[{{ $index }}][waste_quantity]" class="erp-input w-24 text-sm" value="0"></td>
                                        <td><input type="number" step="0.001" min="0" name="materials[{{ $index }}][returned_quantity]" class="erp-input w-24 text-sm" value="0"></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            <div><button type="submit" class="erp-btn-primary">{{ __('Save session') }}</button></div>
        </form>
    </x-admin.card>
@endif

<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Production Sessions') }}</h3>
    @if ($sessions && $sessions->count() > 0)
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Operator') }}</th>
                        <th>{{ __('Start') }}</th>
                        <th>{{ __('End') }}</th>
                        <th>{{ __('Expected') }}</th>
                        <th>{{ __('Produced') }}</th>
                        <th>{{ __('Waste') }}</th>
                        <th>{{ __('Reason') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sessions as $session)
                        <tr>
                            <td>{{ $session->operator?->name ?? '—' }}</td>
                            <td class="tabular-nums">{{ $session->started_at?->format('Y-m-d H:i') }}</td>
                            <td class="tabular-nums">{{ $session->ended_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="tabular-nums">{{ number_format($session->expected_quantity, 0) }}</td>
                            <td class="tabular-nums">{{ number_format($session->produced_quantity, 0) }}</td>
                            <td class="tabular-nums text-red-700">{{ number_format($session->waste_quantity, 0) }}</td>
                            <td>{{ $session->waste_reason?->label() ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($sessions->hasPages())<div class="mt-4">{{ $sessions->links() }}</div>@endif
    @else
        <x-admin.empty-state :title="__('No sessions recorded')" />
    @endif
</x-admin.card>
