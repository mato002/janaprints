@props(['form', 'canManage', 'companyId', 'branchId'])

@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $formsLandingUrl = route('admin.settings.forms.index', $scopeQuery);
@endphp

@include('admin.settings.partials.hub-toolbar', [
    'title' => $form['label'],
    'description' => $form['description'],
    'backUrl' => $formsLandingUrl,
    'backLabel' => __('All forms'),
])

@if (! $canManage)
    <x-admin.card class="mb-4 border-amber-200 bg-amber-50">
        <p class="text-sm text-amber-900">
            <span class="font-semibold">{{ __('View only') }}</span>
            — {{ __('You have settings.view but need settings.manage to edit fields, save changes, or add custom fields. Ask an administrator to grant the Company Admin role or settings.manage permission.') }}
        </p>
    </x-admin.card>
@endif

@if ($canManage)
    <form method="POST" action="{{ route('admin.settings.forms.update') }}" class="space-y-4">
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
])

@if ($canManage)
        <div class="sticky bottom-4 z-10 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-erp-border bg-erp-card px-5 py-4 shadow-lg">
            <p class="text-xs text-slate-500">
                {{ __('Save applies to this form. Built-in fields are defined by the system; custom fields are stored for your tenant.') }}
            </p>
            <x-primary-button>{{ __('Save form settings') }}</x-primary-button>
        </div>
    </form>
@endif
