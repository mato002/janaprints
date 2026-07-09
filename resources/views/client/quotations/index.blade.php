<x-layouts.client :title="__('Quotes')" :heading="__('Quotes')">
    <div class="client-table-wrap">
        <table class="client-table">
            <thead>
                <tr>
                    <th>{{ __('Quote') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Valid until') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotations as $quotation)
                    <tr>
                        <td data-label="{{ __('Quote') }}">{{ $quotation->quotation_number }}</td>
                        <td data-label="{{ __('Date') }}">{{ $quotation->quotation_date?->format('M j, Y') }}</td>
                        <td data-label="{{ __('Valid until') }}">{{ $quotation->valid_until?->format('M j, Y') ?: '—' }}</td>
                        <td data-label="{{ __('Amount') }}">KES {{ number_format((float) $quotation->total_amount, 0) }}</td>
                        <td data-label="{{ __('Status') }}">@include('client.partials.status-badge', ['status' => $quotation->status])</td>
                        <td data-label="{{ __('Action') }}"><a href="{{ route('client.quotations.show', $quotation) }}" class="client-link">{{ __('Open') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="client-empty">{{ __('No quotes available yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $quotations->links() }}
</x-layouts.client>
