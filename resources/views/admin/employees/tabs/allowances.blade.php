<x-admin.card>
    @can('create', App\Models\Hr\EmployeeCompensation::class)
        <form method="POST" action="{{ route('admin.hr.compensation.employee.allowances.store', $employee) }}" class="mb-6 grid gap-3 md:grid-cols-4">
            @csrf
            <select name="allowance_definition_id" class="erp-input">
                <option value="">{{ __('Custom allowance') }}</option>
                @foreach ($allowanceDefinitions as $def)
                    <option value="{{ $def->id }}">{{ $def->name }}</option>
                @endforeach
            </select>
            <input type="text" name="name" class="erp-input" placeholder="{{ __('Name (if custom)') }}">
            <input type="number" step="0.01" name="amount" class="erp-input" placeholder="{{ __('Amount') }}">
            <button type="submit" class="erp-btn-primary">{{ __('Add allowance') }}</button>
        </form>
    @endcan

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Allowance') }}</th>
                <th>{{ __('Type') }}</th>
                <th>{{ __('Amount') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($employee->payrollAllowances as $allowance)
                <tr>
                    <td>{{ $allowance->name }} <span class="text-xs text-slate-500">({{ $allowance->code }})</span></td>
                    <td>{{ $allowance->calculation_type?->label() ?? __('Fixed') }}</td>
                    <td>
                        @if ($allowance->calculation_type?->value === 'percentage')
                            {{ $allowance->percentage_rate }}%
                        @else
                            {{ number_format($allowance->amount, 2) }}
                        @endif
                    </td>
                    <td class="erp-table-actions-col">
                        @can('create', App\Models\Hr\EmployeeCompensation::class)
                            <form method="POST" action="{{ route('admin.hr.compensation.employee.allowances.destroy', [$employee, $allowance]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm text-red-600">{{ __('Remove') }}</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No allowances assigned')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin.card>
