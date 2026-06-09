<x-admin-layout :title="__('Handovers')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Handovers')]]">
    <x-admin.page-header :title="__('Asset Handovers')" :description="__('Formal transfer evidence between employees and branches.')">
        <x-slot name="actions">
            @can('create', \App\Models\Assets\AssetHandover::class)
                <a href="{{ route('admin.assets.custody.handovers.create') }}" class="erp-btn-primary">{{ __('New Handover') }}</a>
            @endcan
        </x-slot>
    </x-admin.page-header>

    <x-admin.card :padding="false" class="mb-4">
        <x-admin.index-toolbar :action="url()->current()" :reset-url="url()->current()">
            <select name="status" class="erp-toolbar-select" aria-label="{{ __('Status') }}">
                <option value="">{{ __('All') }}</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </x-admin.index-toolbar>
    </x-admin.card>

    <x-admin.card>
        <div class="overflow-x-auto">
            <table class="erp-table w-full text-sm">
                <thead>
                    <tr>
                        <th>{{ __('Handover No') }}</th>
                        <th>{{ __('Asset') }}</th>
                        <th>{{ __('From') }}</th>
                        <th>{{ __('To') }}</th>
                        <th>{{ __('Date') }}</th>
                        <th>{{ __('Status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($handovers as $handover)
                        <tr>
                            <td><a href="{{ route('admin.assets.custody.handovers.show', $handover) }}" class="erp-link font-mono">{{ $handover->handover_no }}</a></td>
                            <td>{{ $handover->asset?->asset_name }}</td>
                            <td>{{ $handover->fromEmployee?->full_name ?? $handover->fromBranch?->name ?? '—' }}</td>
                            <td>{{ $handover->toEmployee?->full_name ?? $handover->toBranch?->name ?? '—' }}</td>
                            <td>{{ $handover->handover_date?->format('Y-m-d') }}</td>
                            <td><x-admin.status-badge :variant="$handover->status->badgeVariant()">{{ $handover->status->label() }}</x-admin.status-badge></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No handovers yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($handovers->hasPages())<div class="mt-4">{{ $handovers->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
