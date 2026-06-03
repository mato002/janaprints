@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
    $activeFormKey = request('form');
    $activeForm = $activeFormKey
        ? $forms->first(fn (array $form) => $form['form_key'] === $activeFormKey)
        : null;
@endphp

<x-admin-layout
    :title="$activeForm ? $activeForm['label'] : __('Forms')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Settings'), 'url' => $hubBackUrl],
        ['label' => __('Forms'), 'url' => route('admin.settings.forms.index', $scopeQuery)],
        ...($activeForm ? [['label' => $activeForm['label']]] : []),
    ]"
>
    @if ($activeForm)
        @include('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.forms.index', ['form' => $activeFormKey] + $scopeQuery),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ])

        @include('admin.settings.forms.partials.workspace', [
            'form' => $activeForm,
            'canManage' => $canManage,
            'companyId' => $companyId,
            'branchId' => $branchId,
        ])
    @else
        @include('admin.settings.partials.hub-toolbar', [
            'title' => __('Form Control'),
            'description' => __('Choose a module to configure field visibility, requirements, read-only state, and defaults.'),
            'backUrl' => $hubBackUrl,
        ])

        @include('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.forms.index'),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ])

        @include('admin.settings.forms.partials.landing', [
            'forms' => $forms,
            'companyId' => $companyId,
            'branchId' => $branchId,
        ])
    @endif
</x-admin-layout>
