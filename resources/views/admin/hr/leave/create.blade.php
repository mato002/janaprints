<x-admin.modal-form
    :title="__('Apply for leave')"
    :breadcrumbs="[
        ['label' => __('Leave'), 'url' => route('admin.hr.leave.dashboard')],
        ['label' => __('Apply')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.leave.store')">
        @include('admin.hr.leave.partials.form')
        <x-admin.form-modal-actions>
            <x-primary-button name="submit" value="1">{{ __('Submit leave request') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
