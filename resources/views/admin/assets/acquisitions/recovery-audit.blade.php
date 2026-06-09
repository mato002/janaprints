<x-admin-layout :title="__('Audit History')" :breadcrumbs="[
    ['label' => __('Capitalization Recovery Queue'), 'url' => route('admin.assets.acquisitions.recovery.index')],
    ['label' => $asset->asset_number],
]">
    <x-admin.page-header :title="__('Audit History')" :description="$asset->asset_name" />

    <x-admin.card>
        <dl class="mb-4 grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
            <div><dt class="text-slate-500">{{ __('Asset') }}</dt><dd>{{ $asset->asset_number }}</dd></div>
            <div><dt class="text-slate-500">{{ __('Capitalized By') }}</dt><dd>{{ $asset->capitalizationCandidate?->capitalizer?->name ?? '—' }}</dd></div>
        </dl>

        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('When') }}</th>
                        <th>{{ __('Event') }}</th>
                        <th>{{ __('Description') }}</th>
                        <th>{{ __('User') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asset->financeTimelineEntries as $entry)
                        <tr>
                            <td>{{ $entry->occurred_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $entry->title }}</td>
                            <td>{{ $entry->description ?? '—' }}</td>
                            <td>{{ $entry->user?->name ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-slate-500">{{ __('No finance timeline entries yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <a href="{{ route('admin.assets.acquisitions.recovery.index') }}" class="erp-btn-secondary">{{ __('Back to Queue') }}</a>
        </div>
    </x-admin.card>
</x-admin-layout>
