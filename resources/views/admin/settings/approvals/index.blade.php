@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
    $activeRuleKey = request('rule');
    $activeRule = $activeRuleKey
        ? $rows->first(fn (array $row) => $row['rule_type'] === $activeRuleKey)
        : null;
@endphp

<x-admin-layout
    :title="$activeRule ? $activeRule['label'] : __('Approvals')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Settings'), 'url' => $hubBackUrl],
        ['label' => __('Approvals'), 'url' => route('admin.settings.approvals.index', $scopeQuery)],
        ...($activeRule ? [['label' => $activeRule['label']]] : []),
    ]"
>
    @if ($activeRule)
        @include('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.approvals.index', ['rule' => $activeRuleKey] + $scopeQuery),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ])

        @include('admin.settings.approvals.partials.workspace', [
            'row' => $activeRule,
            'canManage' => $canManage,
            'companyId' => $companyId,
            'branchId' => $branchId,
            'roles' => $roles,
            'permissions' => $permissions,
        ])
    @else
        @include('admin.settings.partials.hub-toolbar', [
            'title' => __('Approval Rules'),
            'description' => __('Choose a rule type to configure amount and discount thresholds, approver roles, and permissions.'),
            'backUrl' => $hubBackUrl,
        ])

        @include('admin.settings.partials.scope-selector', [
            'action' => route('admin.settings.approvals.index'),
            'companyId' => $companyId,
            'branchId' => $branchId,
            'companies' => $companies,
            'branches' => $branches,
        ])

        @include('admin.settings.approvals.partials.landing', [
            'rows' => $rows,
            'companyId' => $companyId,
            'branchId' => $branchId,
        ])
    @endif
</x-admin-layout>
