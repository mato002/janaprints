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
        method="POST"
        action="{{ route('admin.settings.forms.update', $scopeQuery) }}"
        data-turbo-frame="erp-main"
        data-turbo-action="advance"
    >
        @csrf
        @method('PUT')
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <input type="hidden" name="return_form" value="{{ $form['form_key'] }}">
        @if ($branchId)
            <input type="hidden" name="branch_id" value="{{ $branchId }}">
        @endif
@endif

@include('admin.settings.forms.partials.workspace-panel', [
    'form' => $form,
    'canManage' => $canManage,
    'backUrl' => $formsLandingUrl,
])

@if ($canManage)
    </form>
@endif
