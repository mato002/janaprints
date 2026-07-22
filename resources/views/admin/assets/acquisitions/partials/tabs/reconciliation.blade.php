@php
    use App\Support\Navigation\WorkspaceEmbed;
@endphp

<x-admin.card>
    @if (WorkspaceEmbed::inWorkspaceContext())
        @can('assets.reconciliation.view')
            <div class="mb-4 flex justify-end">
                <form method="POST" action="{{ route('admin.assets.acquisitions.reconciliation.store') }}">
                    @csrf
                    <button type="submit" class="erp-btn-primary erp-btn--sm">{{ __('Run reconciliation') }}</button>
                </form>
            </div>
        @endcan
    @endif

    <div class="overflow-x-auto">
        <table class="erp-table w-full text-sm">
            <thead>
                <tr>
                    <th>{{ __('Number') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Procurement') }}</th>
                    <th>{{ __('Capitalized') }}</th>
                    <th>{{ __('Posted') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>{{ $record->reconciliation_number }}</td>
                        <td>{{ $record->reconciliation_date?->format('Y-m-d') }}</td>
                        <td>{{ number_format($record->procurement_received_value, 2) }}</td>
                        <td>{{ number_format($record->capitalized_value, 2) }}</td>
                        <td>{{ number_format($record->posted_value, 2) }}</td>
                        <td><x-admin.status-badge :status="$record->status->value" :label="$record->status->label()" /></td>
                        <td><a href="{{ route('admin.assets.acquisitions.reconciliation.show', $record) }}" class="erp-link" data-turbo-frame="erp-main" data-turbo-action="advance">{{ __('View') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-slate-500">{{ __('No reconciliation runs yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4 border-t border-erp-border pt-3">
        <x-admin.table-pagination :paginator="$records" />
    </div>
</x-admin.card>
