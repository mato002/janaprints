<x-admin-layout :title="__('Permissions')" :breadcrumbs="[['label' => __('Administration')], ['label' => __('Permissions')]]">
    <x-admin.page-header :title="__('Permissions')" :description="__('System permission keys assigned to roles.')" />

    <x-admin.card>
        <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3" role="list">
            @foreach ($permissions as $permission)
                <li class="rounded-lg border border-erp-border bg-erp-page/50 px-3 py-2.5 text-sm font-medium text-slate-700">
                    {{ $permission->name }}
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $permissions->links() }}</div>
    </x-admin.card>
</x-admin-layout>
