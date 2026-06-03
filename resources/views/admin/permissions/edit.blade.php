<x-admin-layout :title="__('Assign permissions')" :breadcrumbs="[['label' => __('Roles'), 'url' => route('admin.roles.index')], ['label' => $role->name]]">
    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('admin.roles.permissions.update', $role) }}">@csrf @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach ($permissions as $permission)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" @checked(in_array($permission->name, $assigned))>
                        {{ $permission->name }}
                    </label>
                @endforeach
            </div>
            <div class="mt-6"><x-primary-button>{{ __('Save permissions') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
