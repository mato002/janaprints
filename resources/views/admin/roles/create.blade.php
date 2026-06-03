<x-admin-layout
    :title="__('Create role')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Access Control'), 'url' => route('admin.access-control.index')],
        ['label' => __('Roles'), 'url' => route('admin.access-control.roles')],
        ['label' => __('Create')],
    ]"
>
    <x-admin.card class="max-w-lg">
        <h2 class="text-base font-semibold text-erp-primary">{{ __('New security group') }}</h2>
        <p class="mt-1 mb-4 text-sm text-slate-500">{{ __('Create a role and optionally clone permissions from an existing group.') }}</p>
        <form method="POST" action="{{ route('admin.roles.store') }}" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="name" :value="__('Role name')" />
                <x-text-input id="name" name="name" class="mt-1 block w-full" placeholder="{{ __('e.g. Production Supervisor') }}" required />
            </div>
            <div>
                <x-input-label for="clone_from" :value="__('Clone from existing role')" />
                <select id="clone_from" name="clone_from" class="erp-select mt-1 w-full">
                    <option value="">{{ __('Start without permissions') }}</option>
                    @foreach ($cloneOptions as $option)
                        <option value="{{ $option->id }}" @selected(old('clone_from') == $option->id)>
                            {{ $option->name }}
                            @if ($option->permissions_count > 0)
                                ({{ $option->permissions_count }} {{ __('permissions') }})
                            @endif
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-slate-500">{{ __('Copies permissions only. Users are never cloned.') }}</p>
            </div>
            <div>
                <x-primary-button>{{ __('Create role') }}</x-primary-button>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
