<x-admin.data-table export-filename="payroll-deductions">
    <x-slot name="head">
        <tr>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Deduction') }}</th>
            <th>{{ __('Employees') }}</th>
            <th>{{ __('Total') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($deductions as $row)
            <tr>
                <td class="font-mono text-xs">{{ $row['code'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td class="tabular-nums">{{ $row['employee_count'] }}</td>
                <td class="tabular-nums font-medium">{{ number_format($row['amount'], 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-admin.empty-state
                        :title="__('No custom deductions')"
                        :description="__('Statutory deductions are shown on the Statutories tab. Custom deductions appear here after generation.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot>
</x-admin.data-table>
