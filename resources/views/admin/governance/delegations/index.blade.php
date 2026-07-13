@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.workspaces.administration.section', ['section' => 'workflow-governance']);
@endphp

<x-admin-layout
    :title="__('Delegations')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance'), 'url' => $hubBackUrl],
        ['label' => __('Delegations')],
    ]"
>
    @include('admin.settings.partials.hub-toolbar', [
        'title' => __('Approval Delegations'),
        'description' => __('Temporarily transfer approval authority while the original approver remains the owner.'),
        'backUrl' => $hubBackUrl,
    ])

    @include('admin.settings.partials.scope-selector', [
        'action' => route('admin.governance.delegations.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
        'branchEmptyLabel' => __('Company-wide'),
    ])

<x-admin.card>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">
                {{ __(':count delegations configured', ['count' => count($rows)]) }}
            </p>
            @if ($canCreate)
                <a href="{{ route('admin.governance.delegations.create', $scopeQuery) }}" class="erp-btn erp-btn--primary">
                    {{ __('Create Delegation') }}
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="py-3 pr-3">{{ __('Delegator') }}</th>
                        <th class="py-3 px-2">{{ __('Delegate') }}</th>
                        <th class="py-3 px-2">{{ __('Modules') }}</th>
                        <th class="py-3 px-2">{{ __('Approval Types') }}</th>
                        <th class="py-3 px-2">{{ __('Reason') }}</th>
                        <th class="py-3 px-2">{{ __('Period') }}</th>
                        <th class="py-3 px-2">{{ __('Status') }}</th>
                        <th class="py-3 pl-2 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-erp-border">
                            <td class="py-3 pr-3 font-medium">{{ $row['delegator'] }}</td>
                            <td class="py-3 px-2">{{ $row['delegate'] }}</td>
                            <td class="py-3 px-2 text-xs">{{ $row['modules'] }}</td>
                            <td class="py-3 px-2 text-xs">{{ $row['approval_types'] }}</td>
                            <td class="py-3 px-2 text-xs">{{ $row['reason'] }}</td>
                            <td class="py-3 px-2 text-xs whitespace-nowrap">
                                {{ $row['start_date'] }} → {{ $row['end_date'] }}
                            </td>
                            <td class="py-3 px-2">
                                @if ($row['is_operational'])
                                    <span class="erp-badge erp-badge--success">{{ $row['status'] }}</span>
                                @elseif ($row['status_key'] === 'scheduled')
                                    <span class="erp-badge erp-badge--info">{{ $row['status'] }}</span>
                                @else
                                    <span class="erp-badge erp-badge--muted">{{ $row['status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3 pl-2 text-right">
                                <div class="flex justify-end gap-2">
                                    @if ($canManage && ! in_array($row['status_key'], ['cancelled', 'expired'], true))
                                        <a href="{{ route('admin.governance.delegations.edit', ['approvalDelegation' => $row['id']] + $scopeQuery) }}" class="erp-btn erp-btn--ghost erp-btn--sm">
                                            {{ __('Edit') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.governance.delegations.cancel', ['approvalDelegation' => $row['id']] + $scopeQuery) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="erp-btn erp-btn--ghost erp-btn--sm text-red-600">
                                                {{ __('Cancel') }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-slate-500">
                                {{ __('No approval delegations configured for this scope.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
