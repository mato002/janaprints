@if (! empty($tabData['restricted']))
    <x-admin.empty-state :title="__('Access restricted')" :description="__('You need dispatch view permission.')" />
@else
    @php($notes = $tabData['notes'])
    <x-admin.data-table :searchable="false" :exportable="false" :filterable="false">
        <x-slot:head>
            <tr>
                <th>{{ __('Delivery note') }}</th>
                <th>{{ __('Date') }}</th>
                <th>{{ __('Status') }}</th>
                <th>{{ __('Job') }}</th>
                <th>{{ __('Sales order') }}</th>
            </tr>
        </x-slot:head>
        <x-slot:body>
            @forelse ($notes as $note)
                <tr>
                    <td>
                        <a href="{{ route('admin.dispatch.delivery-notes.show', $note) }}" class="font-mono text-indigo-600">{{ $note->delivery_note_number }}</a>
                    </td>
                    <td>{{ $note->delivery_date->format('M j, Y') }}</td>
                    <td><x-admin.enum-status-badge :status="$note->status->value" /></td>
                    <td>
                        @if ($note->productionJobCard)
                            <a href="{{ route('admin.production.job-cards.show', $note->productionJobCard) }}">{{ $note->productionJobCard->job_card_number }}</a>
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $note->salesOrder?->order_number ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="py-8 text-center text-slate-500">{{ __('No delivery history.') }}</td></tr>
            @endforelse
        </x-slot:body>
        @if ($notes->hasPages())
            <x-slot:footer><x-admin.table-pagination :paginator="$notes" /></x-slot:footer>
        @endif
    </x-admin.data-table>
@endif
