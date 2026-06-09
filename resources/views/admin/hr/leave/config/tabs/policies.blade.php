@can('create', App\Models\Hr\LeaveType::class)
    <x-admin.card>
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('New leave policy') }}</h3>
        <form method="POST" action="{{ route('admin.hr.leave.config.policies.store') }}" class="grid gap-3 md:grid-cols-3">
            @csrf
            <select name="leave_type_id" class="erp-input" required>
                <option value="">{{ __('Leave type') }}</option>
                @foreach ($leaveTypeOptions as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            <input type="text" name="code" class="erp-input" placeholder="{{ __('Code') }}" required>
            <input type="text" name="name" class="erp-input" placeholder="{{ __('Policy name') }}" required>
            <input type="number" name="min_notice_days" class="erp-input" placeholder="{{ __('Min notice days') }}" value="0">
            <input type="number" step="0.5" name="max_consecutive_days" class="erp-input" placeholder="{{ __('Max consecutive days') }}">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requires_documentation" value="1"> {{ __('Requires documentation') }}</label>
            <textarea name="description" class="erp-input md:col-span-3" rows="2" placeholder="{{ __('Description') }}"></textarea>
            <button type="submit" class="erp-btn-primary">{{ __('Create policy') }}</button>
        </form>
    </x-admin.card>
@endcan

<x-admin.card>
    <x-admin.data-table>
        <x-slot name="head">
            <tr><th>{{ __('Policy') }}</th><th>{{ __('Leave Type') }}</th><th>{{ __('Notice') }}</th><th>{{ __('Max Days') }}</th><th>{{ __('Status') }}</th></tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($policies as $policy)
                <tr>
                    <td>
                        <div class="font-medium">{{ $policy->name }}</div>
                        <div class="text-xs text-slate-500">{{ $policy->code }}</div>
                    </td>
                    <td>{{ $policy->leaveType?->name }}</td>
                    <td>{{ $policy->min_notice_days }} {{ __('days') }}</td>
                    <td>{{ $policy->max_consecutive_days ?? '—' }}</td>
                    <td>{{ $policy->is_active ? __('Active') : __('Inactive') }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state :title="__('No leave policies')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$policies" /></x-slot>
    </x-admin.data-table>
</x-admin.card>
