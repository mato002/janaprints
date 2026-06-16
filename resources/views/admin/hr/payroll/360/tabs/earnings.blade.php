<x-admin.data-table export-filename="payroll-earnings">
    <x-slot name="head">
        <tr>
            <th>{{ __('Code') }}</th>
            <th>{{ __('Earning') }}</th>
            <th>{{ __('Employees') }}</th>
            <th>{{ __('Total') }}</th>
        </tr>
    </x-slot>
    <x-slot name="body">
        @forelse ($earnings as $row)
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
                        :title="__('No earnings yet')"
                        :description="__('Earnings appear after payroll is generated from salary setup and allowances.')"
                    />
                </td>
            </tr>
        @endforelse
    </x-slot>
</x-admin.data-table>
