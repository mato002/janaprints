<x-admin-layout :title="__('Depreciation Runs')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Depreciation Runs')]]">
    <x-admin.page-header :title="__('Depreciation Runs')">
        <x-slot name="actions">
            @can('run', \App\Models\Assets\DepreciationRun::class)
                <a href="{{ route('admin.assets.finance.runs.create') }}" class="erp-btn-primary">{{ __('New Run') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead><tr><th>{{ __('Run No') }}</th><th>{{ __('Period') }}</th><th>{{ __('Status') }}</th><th>{{ __('Total') }}</th><th>{{ __('Assets') }}</th><th>{{ __('Run Date') }}</th></tr></thead>
                <tbody>
                    @forelse ($runs as $run)
                        <tr>
                            <td><a href="{{ route('admin.assets.finance.runs.show', $run) }}" class="erp-link font-mono">{{ $run->run_number }}</a></td>
                            <td>{{ $run->period }}</td>
                            <td><x-admin.status-badge :variant="$run->status->badgeVariant()">{{ $run->status->label() }}</x-admin.status-badge></td>
                            <td>{{ number_format($run->total_depreciation, 2) }}</td>
                            <td>{{ $run->assets_processed }}</td>
                            <td>{{ $run->run_date?->format('Y-m-d') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No depreciation runs yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($runs->hasPages())<div class="mt-4">{{ $runs->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
