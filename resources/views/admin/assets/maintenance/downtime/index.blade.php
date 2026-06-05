<x-admin-layout :title="__('Downtime')" :breadcrumbs="[['label' => __('Assets'), 'url' => route('admin.workspaces.assets')], ['label' => __('Downtime')]]">
    <x-admin.page-header :title="__('Asset Downtime')" :description="__('Downtime records with duration and impact.')" />
    <x-admin.card>
        <table class="erp-table w-full text-sm">
            <thead><tr><th>{{ __('Asset') }}</th><th>{{ __('Work Order') }}</th><th>{{ __('Start') }}</th><th>{{ __('End') }}</th><th>{{ __('Duration') }}</th><th>{{ __('Impact') }}</th></tr></thead>
            <tbody>
                @forelse ($records as $record)
                    <tr>
                        <td>{{ $record->asset?->asset_name }}</td>
                        <td>{{ $record->workOrder?->work_order_no ?? '—' }}</td>
                        <td>{{ $record->start_time?->format('Y-m-d H:i') }}</td>
                        <td>{{ $record->end_time?->format('Y-m-d H:i') ?? __('Active') }}</td>
                        <td>{{ $record->duration_minutes }} min ({{ $record->durationHours() }}h)</td>
                        <td>{{ $record->impact_level->label() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-500">{{ __('No downtime records yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
        @if ($records->hasPages())<div class="mt-4">{{ $records->links() }}</div>@endif
    </x-admin.card>
</x-admin-layout>
