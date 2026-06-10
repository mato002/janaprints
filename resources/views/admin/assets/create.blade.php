<x-admin.modal-form
    :title="__('Register asset')"
    :breadcrumbs="[
        ['label' => __('Assets'), 'url' => route('admin.workspaces.assets')],
        ['label' => __('Asset Register'), 'url' => route('admin.assets.index')],
        ['label' => __('Create')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.assets.store')">
        @include('admin.assets.partials.form-fields', [
            'asset' => null,
            'categories' => $categories,
            'branches' => $branches,
            'users' => $users,
            'statuses' => $statuses,
        ])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Register Asset') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
