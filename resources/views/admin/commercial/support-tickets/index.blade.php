<x-admin-layout :title="__('Support Tickets')" :breadcrumbs="[['label' => __('Commercial')], ['label' => __('Support Tickets')]]">
    <x-admin.page-header :title="__('Support tickets')" :description="__('Help desk cases and SLA tracking.')">
        <x-slot name="actions">
            @can('create', App\Models\Commercial\CommercialSupportTicket::class)
                <a href="{{ route('admin.commercial.support-tickets.create') }}" class="erp-btn-primary">{{ __('New ticket') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.data-table>
        <x-slot name="head">
            <th>{{ __('Ticket') }}</th>
            <th>{{ __('Subject') }}</th>
            <th>{{ __('Customer') }}</th>
            <th>{{ __('Priority') }}</th>
            <th>{{ __('Status') }}</th>
            <th>{{ __('Due') }}</th>
            <th></th>
        </x-slot>
        <x-slot name="body">
            @forelse ($tickets as $ticket)
                <tr>
                    <td class="font-mono text-sm">{{ $ticket->ticket_number }}</td>
                    <td>{{ $ticket->subject }}</td>
                    <td>{{ $ticket->customer?->company_name ?? '—' }}</td>
                    <td>{{ $ticket->priority->label() }}</td>
                    <td>
                        {{ $ticket->status->label() }}
                        @if ($ticket->isOverdue())
                            <x-admin.status-badge variant="danger">{{ __('Overdue') }}</x-admin.status-badge>
                        @endif
                    </td>
                    <td>{{ $ticket->due_at?->format('d M Y H:i') ?? '—' }}</td>
                    <td><a href="{{ route('admin.commercial.support-tickets.show', $ticket) }}" class="erp-btn-secondary text-xs">{{ __('View') }}</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-8 text-center text-slate-500">{{ __('No tickets yet.') }}</td></tr>
            @endforelse
        </x-slot>
    </x-admin.data-table>
    <div class="mt-4">{{ $tickets->links() }}</div>
</x-admin-layout>
