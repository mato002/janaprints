@can('create', App\Models\Hr\LeaveType::class)
    <x-admin.card>
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('New leave type') }}</h3>
        <form method="POST" action="{{ route('admin.hr.leave.config.types.store') }}" class="grid gap-3 md:grid-cols-4">
            @csrf
            <input type="text" name="code" class="erp-input" placeholder="{{ __('Code') }}" required>
            <input type="text" name="name" class="erp-input" placeholder="{{ __('Name') }}" required>
            <select name="unit" class="erp-input">
                @foreach ($units as $unit)
                    <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                @endforeach
            </select>
            <input type="number" step="0.1" name="default_days_per_year" class="erp-input" placeholder="{{ __('Days per year') }}">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_paid" value="1" checked> {{ __('Paid') }}</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requires_supervisor_approval" value="1" checked> {{ __('Supervisor approval') }}</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requires_hr_approval" value="1" checked> {{ __('HR approval') }}</label>
            <button type="submit" class="erp-btn-primary md:col-span-4">{{ __('Create leave type') }}</button>
        </form>
    </x-admin.card>
@endcan

<x-admin.card>
    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Unit') }}</th>
                <th>{{ __('Days/Year') }}</th>
                <th>{{ __('Paid') }}</th>
                <th>{{ __('Approval') }}</th>
                <th>{{ __('Status') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($leaveTypes as $type)
                <tr>
                    <td>
                        <div class="font-medium">{{ $type->name }}</div>
                        <div class="text-xs text-slate-500">{{ $type->code }}</div>
                    </td>
                    <td>{{ $type->unit instanceof \App\Enums\LeaveUnit ? $type->unit->label() : ucfirst((string) ($type->unit ?? 'days')) }}</td>
                    <td>{{ $type->default_days_per_year ?? '—' }}</td>
                    <td>{{ $type->is_paid ? __('Yes') : __('No') }}</td>
                    <td class="text-xs">
                        @if ($type->requires_supervisor_approval) {{ __('Supervisor') }} @endif
                        @if ($type->requires_hr_approval) {{ __('HR') }} @endif
                    </td>
                    <td>{{ $type->is_active ? __('Active') : __('Inactive') }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state :title="__('No leave types')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$leaveTypes" /></x-slot>
    </x-admin.data-table>
</x-admin.card>
