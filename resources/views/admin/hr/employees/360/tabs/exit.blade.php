@if ($exit['active'])
    <x-admin.card class="mb-4">
        <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Active Exit Process') }}</h3>
        <dl class="grid gap-3 sm:grid-cols-2 text-sm">
            <div><dt class="text-xs text-slate-500">{{ __('Type') }}</dt><dd>{{ $exit['active']->exit_type?->label() ?? $exit['active']->exit_type }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Status') }}</dt><dd>{{ $exit['active']->status?->label() ?? $exit['active']->status }}</dd></div>
            <div><dt class="text-xs text-slate-500">{{ __('Last Working Day') }}</dt><dd>{{ $exit['active']->last_working_date?->format('M j, Y') }}</dd></div>
            @php $clearance = $exit['active']->clearanceProgress(); @endphp
            <div><dt class="text-xs text-slate-500">{{ __('Clearance') }}</dt><dd>{{ $clearance['done'] }}/{{ $clearance['total'] }}</dd></div>
        </dl>
        @if ($exit['final_dues'])
            <p class="mt-3 text-sm">{{ __('Estimated final dues: :amount', ['amount' => number_format((float) ($exit['final_dues']['net_final_dues'] ?? 0), 2)]) }}</p>
        @endif
        <a href="{{ route('admin.hr.exit.show', $exit['active']) }}" class="erp-btn-secondary mt-3 inline-flex">{{ __('View exit process') }}</a>
    </x-admin.card>
@endif

<x-admin.card>
    <h3 class="mb-3 font-semibold text-erp-primary">{{ __('Exit History') }}</h3>
    <x-admin.data-table>
        <x-slot name="head"><tr><th>{{ __('Reference') }}</th><th>{{ __('Type') }}</th><th>{{ __('Last Day') }}</th><th>{{ __('Status') }}</th></tr></x-slot>
        <x-slot name="body">
            @forelse ($exit['records'] as $record)
                <tr>
                    <td><a href="{{ route('admin.hr.exit.show', $record) }}" class="text-erp-primary hover:underline">{{ $record->reference }}</a></td>
                    <td>{{ $record->exit_type?->label() ?? $record->exit_type }}</td>
                    <td>{{ $record->last_working_date?->format('M j, Y') }}</td>
                    <td>{{ $record->status?->label() ?? $record->status }}</td>
                </tr>
            @empty
                <tr><td colspan="4"><x-admin.empty-state :title="__('No exit records')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$exit['records']" /></x-slot>
    </x-admin.data-table>
</x-admin.card>
