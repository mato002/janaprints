@php($checks = $tabData['checks'] ?? null)

@if ($tabData['qc_blocking'] ?? false)
    <x-admin.card class="mb-4 border-red-200 bg-red-50">
        <p class="text-sm font-medium text-red-900">{{ __('QC failed — dispatch blocked') }}</p>
        <p class="mt-1 text-sm text-red-800">{{ __('Record a passing QC check before dispatch can proceed.') }}</p>
    </x-admin.card>
@endif

@if ($tabData['can_record'] ?? false)
    <x-admin.card class="mb-6" id="add-qc">
        <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Add QC check') }}</h3>
        <form method="POST" action="{{ route('admin.production.quality-checks.store', $jobCard) }}" class="max-w-md space-y-2">
            @csrf
            <select name="result" class="erp-input w-full text-sm" required>
                <option value="passed">{{ __('Passed') }}</option>
                <option value="failed">{{ __('Failed') }}</option>
                <option value="rework_required">{{ __('Rework required') }}</option>
            </select>
            <textarea name="comments" class="erp-input w-full text-sm" rows="2" placeholder="{{ __('Comments') }}"></textarea>
            <button type="submit" class="erp-btn-primary text-sm">{{ __('Record QC') }}</button>
        </form>
    </x-admin.card>
@endif

<x-admin.card>
    <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-erp-primary">{{ __('Quality checks') }}</h3>
    @if ($checks && $checks->count() > 0)
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Result') }}</th>
                        <th>{{ __('Checked by') }}</th>
                        <th>{{ __('Checked at') }}</th>
                        <th>{{ __('Notes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($checks as $check)
                        @php($failed = in_array($check->result->value, ['failed', 'rework_required'], true))
                        <tr class="{{ $failed ? 'bg-red-50' : '' }}">
                            <td><x-admin.enum-status-badge :status="$check->result->value" /></td>
                            <td>{{ $check->checker?->name ?? '—' }}</td>
                            <td class="tabular-nums">{{ $check->checked_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $check->comments ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if ($checks->hasPages())
            <div class="mt-4">{{ $checks->links() }}</div>
        @endif
    @else
        <x-admin.empty-state :title="__('No QC checks')" :description="__('Quality inspections will appear here once recorded.')" />
    @endif
</x-admin.card>
