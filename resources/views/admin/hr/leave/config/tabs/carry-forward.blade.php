@can('create', App\Models\Hr\LeaveType::class)
    <x-admin.card>
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('New carry forward rule') }}</h3>
        <form method="POST" action="{{ route('admin.hr.leave.config.carry.store') }}" class="grid gap-3 md:grid-cols-4">
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
            <input type="number" step="0.5" name="max_carry_days" class="erp-input" placeholder="{{ __('Maximum carry days') }}" required>
            <input type="number" min="1" max="12" name="expiry_month" class="erp-input" placeholder="{{ __('Expiry month') }}">
            <input type="number" min="1" max="31" name="expiry_day" class="erp-input" placeholder="{{ __('Expiry day') }}">
            <textarea name="policy_notes" class="erp-input md:col-span-2" rows="1" placeholder="{{ __('Policy notes') }}"></textarea>
            <button type="submit" class="erp-btn-primary">{{ __('Create carry rule') }}</button>
        </form>
    </x-admin.card>
@endcan

<x-admin.card>
    <x-admin.data-table>
        <x-slot name="head">
            <tr><th>{{ __('Leave Type') }}</th><th>{{ __('Max Carry') }}</th><th>{{ __('Expiry') }}</th><th>{{ __('Policy') }}</th><th>{{ __('Status') }}</th></tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($carryForwardRules as $rule)
                <tr>
                    <td>{{ $rule->leaveType?->name }}</td>
                    <td>{{ $rule->max_carry_days }}</td>
                    <td>
                        @if ($rule->expiry_month)
                            {{ $rule->expiry_month }}/{{ $rule->expiry_day ?? '—' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-sm text-slate-600">{{ $rule->policy_notes ?? '—' }}</td>
                    <td>{{ $rule->is_active ? __('Active') : __('Inactive') }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state :title="__('No carry forward rules')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$carryForwardRules" /></x-slot>
    </x-admin.data-table>
</x-admin.card>
