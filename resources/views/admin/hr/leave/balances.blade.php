<x-admin-layout :title="__('Leave Balances')" :breadcrumbs="[['label' => __('Leave'), 'url' => route('admin.hr.leave.dashboard')], ['label' => __('Balances')]]">
    <x-admin.page-header :title="__('Leave Balances')" :description="__('Opening balance, earned, taken, pending, and available leave.')">
        <x-slot name="actions">
            <a href="{{ route('admin.hr.leave.dashboard') }}" class="erp-btn-secondary">{{ __('Dashboard') }}</a>
        </x-slot>
    </x-admin.page-header>

    <form method="GET" class="erp-card mb-4 flex items-end gap-3">
        <div>
            <label class="erp-label">{{ __('Year') }}</label>
            <input type="number" name="year" value="{{ $year }}" class="erp-input w-32 text-sm" min="2020" max="2100">
        </div>
        <button type="submit" class="erp-btn-primary">{{ __('Load') }}</button>
    </form>

    <x-admin.data-table export-filename="leave-balances">
        <x-slot name="head">
            <tr>
                <th>{{ __('Employee') }}</th>
                <th>{{ __('Leave Type') }}</th>
                <th>{{ __('Opening') }}</th>
                <th>{{ __('Earned') }}</th>
                <th>{{ __('Taken') }}</th>
                <th>{{ __('Pending') }}</th>
                <th>{{ __('Available') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($balances as $balance)
                <tr>
                    <td class="font-medium">{{ $balance->employee?->full_name }}</td>
                    <td>{{ $balance->leaveType?->name }}</td>
                    <td class="tabular-nums">{{ $balance->opening_balance }}</td>
                    <td class="tabular-nums">{{ $balance->earned }}</td>
                    <td class="tabular-nums">{{ $balance->taken }}</td>
                    <td class="tabular-nums">{{ $balance->pending }}</td>
                    <td class="tabular-nums font-semibold">{{ $balance->available() }}</td>
                </tr>
            @empty
                <tr><td colspan="7"><x-admin.empty-state icon="calendar" :title="__('No leave balances yet')" :description="__('Balances are created when employees apply for leave.')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">
            <x-admin.table-pagination :paginator="$balances" />
        </x-slot>
    </x-admin.data-table>
</x-admin-layout>
