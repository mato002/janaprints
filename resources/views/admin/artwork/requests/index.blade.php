<x-admin-layout :title="__('Artwork requests')" :breadcrumbs="[['label' => __('Artwork'), 'url' => route('admin.artwork.dashboard')], ['label' => __('Requests')]]">
    <x-admin.page-header :title="__('Artwork requests')" :description="__('All design requests for your branch.')">
        @can('create', App\Models\Artwork\ArtworkRequest::class)
            <a href="{{ route('admin.artwork.create') }}" class="erp-btn-primary">{{ __('New request') }}</a>
        @endcan
    </x-admin.page-header>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Number') }}</th>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Customer') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Priority') }}</th>
                        <th>{{ __('Due') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $item)
                        <tr>
                            <td>{{ $item->request_number }}</td>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->customer?->company_name }}</td>
                            <td><span class="erp-badge">{{ str_replace('_', ' ', $item->status->value) }}</span></td>
                            <td>{{ $item->priority->value }}</td>
                            <td>{{ $item->due_date?->format('Y-m-d') ?? '—' }}</td>
                            <td><a href="{{ route('admin.artwork.show', $item) }}" class="text-indigo-600">{{ __('View') }}</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-slate-500 py-4">{{ __('No artwork requests yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $requests->links() }}</div>
    </x-admin.card>
</x-admin-layout>
