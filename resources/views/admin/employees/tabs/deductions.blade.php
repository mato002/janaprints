<x-admin.card>
    @can('create', App\Models\Hr\EmployeeCompensation::class)
        <form method="POST" action="{{ route('admin.hr.compensation.employee.deductions.store', $employee) }}" class="mb-6 grid gap-3 md:grid-cols-4">
            @csrf
            <select name="deduction_definition_id" class="erp-input">
                <option value="">{{ __('Custom deduction') }}</option>
                @foreach ($deductionDefinitions as $def)
                    <option value="{{ $def->id }}">{{ $def->name }}</option>
                @endforeach
            </select>
            <input type="text" name="name" class="erp-input" placeholder="{{ __('Name (if custom)') }}">
            <input type="number" step="0.01" name="amount" class="erp-input" placeholder="{{ __('Amount') }}">
            <button type="submit" class="erp-btn-primary">{{ __('Add deduction') }}</button>
        </form>
    @endcan

    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Deduction') }}</th>
                <th>{{ __('Category') }}</th>
                <th>{{ __('Amount') }}</th>
                <th class="erp-table-actions-col">{{ __('Actions') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($employee->payrollDeductions as $deduction)
                <tr>
                    <td>{{ $deduction->name }} <span class="text-xs text-slate-500">({{ $deduction->code }})</span></td>
                    <td>{{ strtoupper($deduction->category) }}</td>
                    <td>
                        @if ($deduction->calculation_type?->value === 'percentage')
                            {{ $deduction->percentage_rate }}%
                        @else
                            {{ number_format($deduction->amount, 2) }}
                        @endif
                    </td>
                    <td class="erp-table-actions-col">
                        @can('create', App\Models\Hr\EmployeeCompensation::class)
                            <form method="POST" action="{{ route('admin.hr.compensation.employee.deductions.destroy', [$employee, $deduction]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-sm text-red-600">{{ __('Remove') }}</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No deductions assigned')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin.card>
