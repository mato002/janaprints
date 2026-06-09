@can('create', App\Models\Hr\LeaveType::class)
    <x-admin.card>
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('New accrual rule') }}</h3>
        <form method="POST" action="{{ route('admin.hr.leave.config.accrual.store') }}" class="grid gap-3 md:grid-cols-4">
            @csrf
            <select name="leave_type_id" class="erp-input" required>
                <option value="">{{ __('Leave type') }}</option>
                @foreach ($leaveTypeOptions as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            <select name="leave_policy_id" class="erp-input">
                <option value="">{{ __('Policy (optional)') }}</option>
                @foreach ($policyOptions as $policy)
                    <option value="{{ $policy->id }}">{{ $policy->name }}</option>
                @endforeach
            </select>
            <select name="frequency" class="erp-input" required>
                @foreach ($frequencies as $freq)
                    <option value="{{ $freq->value }}">{{ $freq->label() }}</option>
                @endforeach
            </select>
            <input type="number" step="0.01" name="rate_per_period" class="erp-input" placeholder="{{ __('Rate per period') }}" required>
            <input type="number" name="custom_interval_days" class="erp-input" placeholder="{{ __('Custom interval (days)') }}">
            <input type="date" name="effective_from" class="erp-input">
            <button type="submit" class="erp-btn-primary">{{ __('Create accrual rule') }}</button>
        </form>
    </x-admin.card>
@endcan

<x-admin.card>
    <x-admin.data-table>
        <x-slot name="head">
            <tr><th>{{ __('Leave Type') }}</th><th>{{ __('Frequency') }}</th><th>{{ __('Rate') }}</th><th>{{ __('Effective') }}</th><th>{{ __('Status') }}</th></tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($accrualRules as $rule)
                <tr>
                    <td>{{ $rule->leaveType?->name }}</td>
                    <td>{{ $rule->frequency instanceof \App\Enums\LeaveAccrualFrequency ? $rule->frequency->label() : ucfirst((string) ($rule->frequency ?? '')) }}</td>
                    <td>{{ $rule->rate_per_period }}</td>
                    <td>{{ $rule->effective_from?->format('M j, Y') ?? '—' }}</td>
                    <td>{{ $rule->is_active ? __('Active') : __('Inactive') }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state :title="__('No accrual rules')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$accrualRules" /></x-slot>
    </x-admin.data-table>
</x-admin.card>
