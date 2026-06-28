@php
    $checks = $tabData['checks'] ?? null;
    $snapshot = $tabData['snapshot'] ?? null;
    $rework = $tabData['rework_summary'] ?? [];
    $serials = $tabData['serial_ranges'] ?? [];
    $checklistItems = $snapshot?->checklist_items ?? [];
@endphp

@if ($tabData['qc_blocking'] ?? false)
    <x-admin.card class="mb-4 border-red-200 bg-red-50">
        <p class="text-sm font-medium text-red-900">{{ __('QC failed or awaiting approval — dispatch blocked') }}</p>
    </x-admin.card>
@endif

@if ($pending = ($tabData['pending_customer_approval'] ?? null))
    <x-admin.card class="mb-4 border-amber-200 bg-amber-50">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm text-amber-900">{{ __('Awaiting customer approval for conditional pass inspection.') }}</p>
            @if ($tabData['can_approve_customer'] ?? false)
                <form method="POST" action="{{ route('admin.production.quality-checks.approve-customer', [$jobCard, $pending]) }}">
                    @csrf
                    <button type="submit" class="erp-btn-primary text-sm">{{ __('Record customer approval') }}</button>
                </form>
            @endif
        </div>
    </x-admin.card>
@endif

@if (! empty($serials['allocated_start']))
    <x-admin.card class="mb-4">
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Serial ranges') }}</h3>
        <dl class="grid grid-cols-2 gap-2 text-sm md:grid-cols-4">
            <div><dt class="text-slate-500">{{ __('Allocated') }}</dt><dd class="font-mono tabular-nums">{{ $serials['allocated_start'] }} – {{ $serials['allocated_end'] }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Produced to') }}</dt><dd class="font-mono tabular-nums">{{ $serials['produced_end'] ?? '—' }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Spoiled qty') }}</dt><dd class="tabular-nums">{{ $serials['spoiled_quantity'] ?? 0 }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Production loss') }}</dt><dd class="tabular-nums">{{ $serials['loss_metrics']['production_loss_quantity'] ?? 0 }}</dd></div>
        </dl>
        @if (($serials['spoiled_ranges'] ?? collect())->isNotEmpty())
            <table class="erp-table mt-3 w-full text-sm">
                <thead><tr><th>{{ __('Spoiled range') }}</th><th>{{ __('Qty') }}</th></tr></thead>
                <tbody>
                    @foreach ($serials['spoiled_ranges'] as $range)
                        <tr>
                            <td class="font-mono">{{ $range->serial_start }} – {{ $range->serial_end }}</td>
                            <td class="tabular-nums">{{ $range->quantity }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </x-admin.card>
@endif

@if ($tabData['can_record'] ?? false)
    <x-admin.card class="mb-6" id="add-qc" x-data="{ decision: 'passed' }">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Record inspection') }}</h3>
        <form method="POST" action="{{ route('admin.production.quality-checks.store', $jobCard) }}" class="space-y-3">
            @csrf
            <div class="grid grid-cols-1 gap-3 md:grid-cols-3">
                <div>
                    <label class="erp-label text-xs">{{ __('Inspection date') }}</label>
                    <input type="date" name="inspection_date" class="erp-input w-full text-sm" value="{{ now()->toDateString() }}">
                </div>
                <div>
                    <label class="erp-label text-xs">{{ __('Decision') }}</label>
                    <select name="result" class="erp-input w-full text-sm" required x-model="decision">
                        <option value="passed">{{ __('Pass') }}</option>
                        <option value="failed">{{ __('Fail') }}</option>
                        <option value="conditional_pass">{{ __('Conditional pass') }}</option>
                    </select>
                </div>
                <div x-show="decision === 'conditional_pass'" x-cloak>
                    <label class="inline-flex items-center gap-2 text-sm mt-6">
                        <input type="checkbox" name="requires_customer_approval" value="1">
                        {{ __('Requires customer approval') }}
                    </label>
                </div>
            </div>

            @if (count($checklistItems) > 0)
                <div>
                    <h4 class="mb-2 text-xs font-semibold uppercase text-slate-600">{{ __('Checklist') }}</h4>
                    <table class="erp-table w-full text-sm">
                        <thead><tr><th>{{ __('Item') }}</th><th>{{ __('Pass') }}</th><th>{{ __('Fail') }}</th></tr></thead>
                        <tbody>
                            @foreach ($checklistItems as $index => $item)
                                <tr>
                                    <td>
                                        {{ $item['label'] }}
                                        <input type="hidden" name="checklist[{{ $index }}][line_id]" value="{{ $item['line_id'] ?? '' }}">
                                        <input type="hidden" name="checklist[{{ $index }}][label]" value="{{ $item['label'] }}">
                                    </td>
                                    <td><input type="radio" name="checklist[{{ $index }}][passed]" value="1"></td>
                                    <td><input type="radio" name="checklist[{{ $index }}][passed]" value="0"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2" x-show="decision === 'failed' || decision === 'conditional_pass'" x-cloak>
                <div>
                    <label class="erp-label text-xs">{{ __('Fail reason') }}</label>
                    <select name="fail_reason" class="erp-input w-full text-sm">
                        <option value="">{{ __('—') }}</option>
                        @foreach ($tabData['fail_reasons'] ?? [] as $reason)
                            <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label text-xs">{{ __('Rework reason') }}</label>
                    <select name="rework_reason" class="erp-input w-full text-sm">
                        <option value="">{{ __('—') }}</option>
                        @foreach ($tabData['rework_reasons'] ?? [] as $reason)
                            <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="erp-label text-xs">{{ __('Est. rework qty') }}</label>
                    <input type="number" step="0.001" min="0" name="estimated_rework_qty" class="erp-input w-full text-sm">
                </div>
                <div>
                    <label class="erp-label text-xs">{{ __('Actual rework qty') }}</label>
                    <input type="number" step="0.001" min="0" name="actual_rework_qty" class="erp-input w-full text-sm">
                </div>
            </div>

            <div>
                <label class="erp-label text-xs">{{ __('Notes') }}</label>
                <textarea name="comments" class="erp-input w-full text-sm" rows="2"></textarea>
            </div>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Save inspection') }}</button>
        </form>
    </x-admin.card>
@endif

@if (($rework['count'] ?? 0) > 0)
    <x-admin.card class="mb-4">
        <h3 class="mb-2 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Rework summary') }}</h3>
        <p class="mb-2 text-sm text-slate-600">{{ __('Est. total') }}: {{ $rework['estimated_total'] }} · {{ __('Actual total') }}: {{ $rework['actual_total'] }}</p>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Reason') }}</th>
                        <th>{{ __('Est. qty') }}</th>
                        <th>{{ __('Actual qty') }}</th>
                        <th>{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rework['lines'] ?? [] as $line)
                        <tr>
                            <td class="tabular-nums">{{ $line['inspection_date'] }}</td>
                            <td>{{ $line['rework_reason'] }}</td>
                            <td class="tabular-nums">{{ $line['estimated_rework_qty'] }}</td>
                            <td class="tabular-nums">{{ $line['actual_rework_qty'] }}</td>
                            <td>{{ $line['notes'] ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-admin.card>
@endif

<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Inspection history') }}</h3>
    @if ($checks && $checks->count() > 0)
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Decision') }}</th>
                        <th>{{ __('Inspector') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Fail / rework') }}</th>
                        <th>{{ __('Rework qty') }}</th>
                        <th>{{ __('Customer approval') }}</th>
                        <th>{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($checks as $check)
                        @php($failed = $check->result->isBlocking())
                        <tr class="{{ $failed && ! $check->customer_approved_at ? 'bg-red-50' : '' }}">
                            <td><x-admin.enum-status-badge :status="$check->result->value" /></td>
                            <td>{{ $check->checker?->name ?? '—' }}</td>
                            <td class="tabular-nums">{{ $check->inspection_date?->format('Y-m-d') ?? $check->checked_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $check->rework_reason?->label() ?? $check->fail_reason?->label() ?? '—' }}</td>
                            <td class="tabular-nums">{{ $check->estimated_rework_qty ?? '—' }} / {{ $check->actual_rework_qty ?? '—' }}</td>
                            <td>
                                @if ($check->customer_approved_at)
                                    {{ $check->customerApprover?->name }} · {{ $check->customer_approved_at->format('Y-m-d') }}
                                @elseif ($check->requires_customer_approval)
                                    {{ __('Pending') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $check->comments ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($checks->hasPages())<div class="mt-4">{{ $checks->links() }}</div>@endif
    @else
        <x-admin.empty-state :title="__('No inspections')" :description="__('Quality inspections will appear here once recorded.')" />
    @endif
</x-admin.card>
