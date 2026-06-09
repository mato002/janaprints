@can('create', App\Models\Hr\LeaveType::class)
    <x-admin.card>
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('New public holiday') }}</h3>
        <form method="POST" action="{{ route('admin.hr.leave.config.holidays.store') }}" class="grid gap-3 md:grid-cols-4">
            @csrf
            <input type="text" name="name" class="erp-input" placeholder="{{ __('Holiday name') }}" required>
            <input type="date" name="holiday_date" class="erp-input" required>
            <input type="text" name="region" class="erp-input" placeholder="{{ __('Region') }}">
            <select name="branch_id" class="erp-input">
                <option value="">{{ __('All branches') }}</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_recurring" value="1"> {{ __('Recurring annually') }}</label>
            <button type="submit" class="erp-btn-primary">{{ __('Add holiday') }}</button>
        </form>
    </x-admin.card>
@endcan

<x-admin.card>
    <x-admin.data-table>
        <x-slot name="head">
            <tr><th>{{ __('Holiday') }}</th><th>{{ __('Date') }}</th><th>{{ __('Region') }}</th><th>{{ __('Branch') }}</th><th>{{ __('Recurring') }}</th><th>{{ __('Status') }}</th></tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($holidays as $holiday)
                <tr>
                    <td>{{ $holiday->name }}</td>
                    <td>{{ $holiday->holiday_date?->format('M j, Y') }}</td>
                    <td>{{ $holiday->region ?? '—' }}</td>
                    <td>{{ $holiday->branch?->name ?? __('All') }}</td>
                    <td>{{ $holiday->is_recurring ? __('Yes') : __('No') }}</td>
                    <td>{{ $holiday->is_active ? __('Active') : __('Inactive') }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state :title="__('No public holidays')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$holidays" /></x-slot>
    </x-admin.data-table>
</x-admin.card>
