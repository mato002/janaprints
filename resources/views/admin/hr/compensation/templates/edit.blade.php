<x-admin.modal-form
    :title="__('Edit payroll class')"
    :breadcrumbs="[
        ['label' => __('HR'), 'url' => route('admin.workspaces.hr')],
        ['label' => __('Compensation'), 'url' => route('admin.hr.compensation.dashboard')],
        ['label' => __('Payroll classes'), 'url' => route('admin.hr.compensation.templates')],
        ['label' => $template->name],
    ]"
    maxWidth="3xl"
>
    <p class="mb-4 text-sm text-slate-500">
        {{ __('Changes apply to future assignments. Employees already on this class keep their current pay until revised in the salary register.') }}
        @if (($usageCount ?? 0) > 0)
            <span class="mt-1 block font-medium text-amber-700">
                {{ trans_choice(':count employee uses this class.|:count employees use this class.', $usageCount, ['count' => $usageCount]) }}
            </span>
        @endif
    </p>

    <x-admin.form-shell :action="route('admin.hr.compensation.templates.update', $template)" method="PUT">
        @include('admin.hr.compensation.templates.partials.form-fields', ['template' => $template])
        <x-admin.form-modal-actions>
            <x-primary-button>{{ __('Save changes') }}</x-primary-button>
        </x-admin.form-modal-actions>
    </x-admin.form-shell>
</x-admin.modal-form>
