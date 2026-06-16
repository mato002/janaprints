<x-layouts.client :title="__('Artwork')" :heading="__('Artwork approvals')">
    <div class="client-table-wrap">
        <table class="client-table">
            <thead>
                <tr>
                    <th>{{ __('Request') }}</th>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Due') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($requests as $request)
                    <tr>
                        <td>{{ $request->request_number }}</td>
                        <td>{{ $request->title }}</td>
                        <td>{{ $request->due_date?->format('M j, Y') ?: '—' }}</td>
                        <td>@include('client.partials.status-badge', ['status' => $request->status])</td>
                        <td><a href="{{ route('client.artwork.show', $request) }}" class="client-link">{{ __('Open') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="client-empty">{{ __('No artwork requests yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $requests->links() }}
</x-layouts.client>
