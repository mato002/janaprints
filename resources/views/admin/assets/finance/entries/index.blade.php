<x-admin-layout :title="__('Depreciation Entries')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Depreciation Entries')]]">
    <x-admin.page-header :title="__('Depreciation Entry Register')" />
    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Period') }}</th><th>{{ __('Asset') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Accumulated') }}</th><th>{{ __('NBV') }}</th><th>{{ __('Status') }}</th><th>{{ __('Journal') }}</th></tr></thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td>{{ $entry->period_date?->format('Y-m') }}</td>
                            <td>{{ $entry->asset?->asset_number }}</td>
                            <td>{{ number_format($entry->depreciation_amount, 2) }}</td>
                            <td>{{ number_format($entry->accumulated_after, 2) }}</td>
                            <td>{{ number_format($entry->net_book_value_after, 2) }}</td>
                            <td>{{ $entry->posting_status->label() }}</td>
                            <td>{{ $entry->journal?->reference ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-slate-500">{{ __('No depreciation entries yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($entries->hasPages())<div class="mt-4">{{ $entries->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
