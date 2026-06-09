<x-admin.card>
    <x-admin.data-table>
        <x-slot name="head">
            <tr>
                <th>{{ __('Effective') }}</th>
                <th>{{ __('Basic Salary') }}</th>
                <th>{{ __('Gross') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Changed By') }}</th>
                <th>{{ __('Reason') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($compensationHistory as $record)
                <tr>
                    <td>{{ $record->effective_from?->format('M j, Y') }}</td>
                    <td>{{ number_format($record->basic_salary, 2) }}</td>
                    <td>{{ number_format($record->grossComponents(), 2) }}</td>
                    <td><span class="erp-badge erp-badge--{{ $record->status?->badgeClass() }}">{{ $record->status?->label() }}</span></td>
                    <td>{{ $record->changedBy?->name ?? '—' }}</td>
                    <td>{{ $record->change_reason ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><x-admin.empty-state :title="__('No compensation history')" /></td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>

    @if ($salaryChanges->isNotEmpty())
        <h3 class="mt-6 mb-3 font-semibold text-erp-primary">{{ __('Salary Revisions') }}</h3>
        <x-admin.data-table>
            <x-slot name="head">
                <tr>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Old') }}</th>
                    <th>{{ __('New') }}</th>
                    <th>{{ __('By') }}</th>
                    <th>{{ __('Reason') }}</th>
                </tr>
            </x-slot>
            <x-slot name="body">
                @foreach ($salaryChanges as $change)
                    <tr>
                        <td>{{ $change->effective_from?->format('M j, Y') }}</td>
                        <td>{{ number_format($change->old_salary, 2) }}</td>
                        <td>{{ number_format($change->new_salary, 2) }}</td>
                        <td>{{ $change->changedBy?->name ?? '—' }}</td>
                        <td>{{ $change->reason ?? '—' }}</td>
                    </tr>
                @endforeach
            </x-slot>
        </x-admin.data-table>
    @endif
</x-admin.card>
