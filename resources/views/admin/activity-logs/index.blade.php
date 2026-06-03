<x-admin-layout :title="__('Activity logs')" :breadcrumbs="[['label' => __('Administration')], ['label' => __('Activity logs')]]">
    <x-admin.page-header :title="__('Activity logs')" :description="__('Audit trail of user and system actions.')" />

    <x-admin.data-table :exportable="true">
        <x-slot name="head">
            <tr>
                <th scope="col">{{ __('When') }}</th>
                <th scope="col">{{ __('User') }}</th>
                <th scope="col">{{ __('Action') }}</th>
                <th scope="col" class="hidden lg:table-cell">{{ __('Model') }}</th>
                <th scope="col" class="hidden md:table-cell">{{ __('IP') }}</th>
            </tr>
        </x-slot>
        <x-slot name="body">
            @forelse ($logs as $log)
                <tr x-show="matches(@js(($log->user?->name ?? '').' '.$log->action.' '.($log->model_type ? class_basename($log->model_type) : '')))">
                    <td class="text-slate-500">{{ $log->created_at }}</td>
                    <td class="font-medium">{{ $log->user?->name ?? '—' }}</td>
                    <td>{{ $log->action }}</td>
                    <td class="hidden lg:table-cell">{{ $log->model_type ? class_basename($log->model_type).' #'.$log->model_id : '—' }}</td>
                    <td class="hidden md:table-cell font-mono text-xs">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="clock" :title="__('No activity recorded yet')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer">{{ $logs->links() }}</x-slot>
    </x-admin.data-table>
</x-admin-layout>
