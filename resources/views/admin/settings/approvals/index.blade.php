@php
    use App\Support\Navigation\WorkspaceEmbed;

    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.settings.show', ['section' => 'hub'] + $scopeQuery);
    $activeRuleKey = request('rule');
    $activeRule = $activeRuleKey
        ? $rows->first(fn (array $row) => $row['rule_type'] === $activeRuleKey)
        : null;
    $embedded = WorkspaceEmbed::isEmbedded();
@endphp

<x-admin-layout
    :title="$activeRule ? $activeRule['label'] : __('Approval Rules')"
    :breadcrumbs="$embedded ? [] : [
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance')],
        ...($activeRule ? [['label' => $activeRule['label']]] : []),
    ]"
    :use-workspace-navigation="! $embedded"
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
        @unless ($embedded)
            @include('admin.settings.partials.hub-toolbar', [
                'title' => __('Approval Rules'),
                'description' => __('Choose a rule type to configure amount and discount thresholds, approver roles, and permissions.'),
                'backUrl' => $hubBackUrl,
            ])
        @endunless

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
