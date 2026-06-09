<x-admin.card class="mb-4">
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Leave Balances') }}</h3>
    <x-admin.data-table>
        <x-slot name="head"><tr><th>{{ __('Type') }}</th><th>{{ __('Available') }}</th><th>{{ __('Taken') }}</th><th>{{ __('Pending') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($leave['balances'] as $balance)
                <tr>
                    <td>{{ $balance['leave_type'] }}</td>
                    <td>{{ $balance['available'] }}</td>
                    <td>{{ $balance['taken'] }}</td>
                    <td>{{ $balance['pending'] }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No leave balances')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
</x-admin.card>

@if ($leave['pending']->isNotEmpty())
    <x-admin.card class="mb-4">
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Pending Requests') }}</h3>
        <ul class="space-y-2 text-sm">
            @foreach ($leave['pending'] as $request)
                <li>
                    <a href="{{ route('admin.hr.leave.show', $request) }}" class="text-erp-primary hover:underline">
                        {{ $request->leaveType?->name }} · {{ $request->start_date?->format('M j') }} – {{ $request->end_date?->format('M j, Y') }}
                    </a>
                </li>
            @endforeach
        </ul>
    </x-admin.card>
@endif

<x-admin.card>
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Leave History') }}</h3>
    <x-admin.data-table>
        <x-slot name="head"><tr><th>{{ __('Type') }}</th><th>{{ __('Period') }}</th><th>{{ __('Days') }}</th><th>{{ __('Status') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($leave['history'] as $request)
                <tr>
                    <td>{{ $request->leaveType?->name }}</td>
                    <td>{{ $request->start_date?->format('M j') }} – {{ $request->end_date?->format('M j, Y') }}</td>
                    <td>{{ $request->days_requested }}</td>
                    <td>{{ $request->status?->label() ?? $request->status }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No leave history')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$leave['history']" /></x-slot>
    </x-admin.data-table>
</x-admin.card>
