<x-admin.modal-form
    :title="__('New appraisal')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Performance'), 'url' => route('admin.hr.performance.dashboard')],
        ['label' => __('New')],
    ]"
    maxWidth="3xl"
>
    <x-admin.form-shell :action="route('admin.hr.performance.store')">
        @include('admin.hr.performance.partials.form')
        <x-admin.form-modal-actions>
            <button type="submit" class="erp-btn-primary">{{ __('Save draft') }}</button>
            <button type="submit" name="submit" value="1" class="erp-btn-secondary">{{ __('Save & submit') }}</button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
