<x-admin.modal-form
    :title="__('Edit activity')"
    :breadcrumbs="[['label' => __('Activities'), 'url' => route('admin.commercial.activities.index')], ['label' => __('Edit')]]"
    maxWidth="4xl"
>
    <x-admin.form-shell :action="route('admin.commercial.activities.update', $activity)" method="PUT">
        @include('admin.commercial.activities.partials.form', ['activity' => $activity])
        <x-admin.form-actions>
            <x-primary-button>{{ __('Update') }}</x-primary-button>
        </x-admin.form-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
