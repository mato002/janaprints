@php($fields = $formFields ?? [])
<x-admin-layout :title="__('Store managers')" :breadcrumbs="[['label' => __('Supply Chain'), 'url' => route('admin.workspaces.supply-chain')], ['label' => __('Store Management'), 'url' => route('admin.inventory.store.dashboard')], ['label' => __('Warehouses'), 'url' => route('admin.inventory.warehouses.index')], ['label' => __('Managers')]]">
    <x-admin.page-header :title="__('Store managers')" :description="$warehouse->name" />

    <div class="bg-white shadow rounded-lg p-6 max-w-4xl">
        <form method="POST" action="{{ route('admin.inventory.warehouses.managers.update', $warehouse) }}">
            @csrf
            @method('PUT')
            @if (($fields['manager_ids']['visible'] ?? true))
                <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
                    @foreach ($users as $user)
                        <label class="flex items-center justify-between gap-3 rounded border border-slate-200 p-3 text-sm">
                            <span>
                                <span class="block font-medium">{{ $user->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $user->email }}</span>
                            </span>
                            <input type="checkbox" name="manager_ids[]" value="{{ $user->id }}" @checked($warehouse->managers->contains('id', $user->id)) @disabled($fields['manager_ids']['read_only'] ?? false)>
                        </label>
                    @endforeach
                </div>
            @endif
            @include('admin.partials.form-custom-fields', ['fields' => $fields, 'model' => null])
            <div class="mt-6"><x-primary-button>{{ __('Save managers') }}</x-primary-button></div>
        </form>
    </div>
</x-admin-layout>
