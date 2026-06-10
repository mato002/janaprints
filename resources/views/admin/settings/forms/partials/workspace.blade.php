@props(['form', 'canManage', 'companyId', 'branchId'])

@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $formsLandingUrl = route('admin.settings.forms.index', $scopeQuery);
@endphp

@if (! $canManage)
    <x-admin.card class="mb-3 border-amber-200 bg-amber-50 !p-3">
        <p class="text-sm text-amber-900">
            <span class="font-semibold">{{ __('View only') }}</span>
            — {{ __('You have settings.view but need settings.manage to edit fields, save changes, or add custom fields. Ask an administrator to grant the Company Admin role or settings.manage permission.') }}
        </p>
    </x-admin.card>
@endif

@if ($canManage)
    <form
        method="post"
        action="{{ route('admin.settings.forms.update') }}"
        data-turbo="false"
        data-erp-form-settings
        data-erp-form-key="{{ $form['form_key'] }}"
        data-erp-form-label="{{ $form['label'] }}"
    >
        @csrf
        @method('PUT')
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <input type="hidden" name="return_form" value="{{ $form['form_key'] }}">
        <input type="hidden" name="form" value="{{ $form['form_key'] }}">
        <input type="hidden" name="branch_id" value="{{ $branchId ?? '' }}">
@endif

@include('admin.settings.forms.partials.workspace-panel', [
    'form' => $form,
    'canManage' => $canManage,
    'backUrl' => $formsLandingUrl,
])

@if ($canManage)
    </form>

    @include('admin.settings.forms.partials.form-settings-submit-script', ['formKey' => $form['form_key']])
@endif
