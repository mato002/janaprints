@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $backUrl = route('admin.governance.delegations.index', $scopeQuery);
    $delegation = new \App\Models\Platform\ApprovalDelegation();
@endphp

<x-admin-layout
    :title="__('Create Delegation')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Delegations'), 'url' => $backUrl],
        ['label' => __('Create')],
    ]"
>
    @include('admin.settings.partials.hub-toolbar', [
        'title' => __('Create Approval Delegation'),
        'description' => __('Transfer temporary approval authority to a substitute approver.'),
        'backUrl' => $backUrl,
    ])

    <x-admin.card>
        <form method="POST" action="{{ route('admin.governance.delegations.store') }}">
            @csrf
            <input type="hidden" name="company_id" value="{{ $companyId }}">
            @if ($branchId)
                <input type="hidden" name="branch_id" value="{{ $branchId }}">
            @endif

            @include('admin.governance.delegations.partials.form')

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ $backUrl }}" class="erp-btn erp-btn--ghost">{{ __('Cancel') }}</a>
                <button type="submit" class="erp-btn erp-btn--primary">{{ __('Create Delegation') }}</button>
            </div>
        </form>
    </x-admin.card>
</x-admin-layout>
