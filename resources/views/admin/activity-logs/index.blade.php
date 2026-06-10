<x-admin-layout
    :title="__('Activity logs')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('System Operations'), 'url' => route('admin.workspaces.administration.section', ['section' => 'system-operations'])],
        ['label' => __('Activity logs')],
    ]"
>
    <x-admin.page-header
        :title="__('Activity logs')"
        :description="__('Audit trail of user and system actions across the ERP platform.')"
    />

    <x-admin.data-table
        :search-placeholder="__('Search activity logs…')"
        export-filename="activity-logs"
        export-route="admin.administration.exports"
        :export-route-params="['listing' => 'activity-logs']"
        :export-query="request()->query()"
        :format-in-path="true"
    >
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
                <tr x-show="rowVisible(@js(strtolower(($log->user?->name ?? '').' '.$log->action.' '.($log->model_type ? class_basename($log->model_type) : '').' '.($log->ip_address ?? ''))))">
                    <td class="text-slate-500">{{ $log->created_at }}</td>
                    <td class="font-medium">{{ $log->user?->name ?? '—' }}</td>
                    <td>{{ $log->action }}</td>
                    <td class="hidden lg:table-cell">{{ $log->model_type ? class_basename($log->model_type).' #'.$log->model_id : '—' }}</td>
                    <td class="hidden md:table-cell font-mono text-xs">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="5"><x-admin.empty-state icon="clock" :title="__('No activity recorded yet')" :description="__('User and system actions will appear here as they occur.')" /></td></tr>
            @endforelse
        </x-slot>
        <x-slot name="footer"><x-admin.table-pagination :paginator="$logs" /></x-slot>
    </x-admin.data-table>
</x-admin-layout>
