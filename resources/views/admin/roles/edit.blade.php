<x-admin-layout
    :title="__('Edit role')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Access Control'), 'url' => route('admin.access-control.index')],
        ['label' => __('Roles'), 'url' => route('admin.access-control.roles')],
        ['label' => $role->name, 'url' => route('admin.roles.show', $role)],
        ['label' => __('Rename')],
    ]"
>
    <x-admin.card class="max-w-md">
        <form method="POST" action="{{ route('admin.roles.update', $role) }}">
            @csrf @method('PUT')
            <x-input-label for="name" :value="__('Role name')" />
            <x-text-input id="name" name="name" class="mt-1 block w-full mb-4" :value="old('name', $role->name)" required />
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </form>
        @can('delete', $role)
            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="mt-6 border-t border-erp-border pt-6" onsubmit="return confirm('{{ __('Delete this role?') }}')">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm font-medium text-red-600 hover:underline">{{ __('Delete role') }}</button>
            </form>
        @endcan
    </x-admin.card>
</x-admin-layout>
