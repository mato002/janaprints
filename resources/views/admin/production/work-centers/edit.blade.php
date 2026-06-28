<x-admin.modal-form
    :title="__('Edit work center')"
    :breadcrumbs="[
        ['label' => __('Production'), 'url' => route('admin.workspaces.production')],
        ['label' => __('Work Centers'), 'url' => route('admin.production.work-centers.index')],
        ['label' => $workCenter->name],
    ]"
    maxWidth="2xl"
>
    <x-admin.form-shell :action="route('admin.production.work-centers.update', $workCenter)" method="PUT">
        @include('admin.production.work-centers.partials.fields', ['workCenter' => $workCenter])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Save') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
