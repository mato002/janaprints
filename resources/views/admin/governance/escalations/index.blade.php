@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $hubBackUrl = route('admin.workspaces.administration.section', ['section' => 'workflow-governance']);
@endphp

<x-admin-layout
    :title="__('Escalations')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance'), 'url' => $hubBackUrl],
        ['label' => __('Escalations')],
    ]"
>
    @include('admin.settings.partials.hub-toolbar', [
        'title' => __('Workflow Escalations'),
        'description' => __('Prevent approval bottlenecks with timeout rules, reminders, and auto-escalation routing.'),
        'backUrl' => $hubBackUrl,
    ])

    @include('admin.settings.partials.scope-selector', [
        'action' => route('admin.governance.escalations.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
        'branchEmptyLabel' => __('Company-wide'),
    ])

<div class="mb-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-admin.kpi-widget :label="__('Total Rules')" :value="$metrics['total']" icon="exclamation" />
        <x-admin.kpi-widget :label="__('Active')" :value="$metrics['active']" icon="badge-check" />
        <x-admin.kpi-widget :label="__('Reminder Rules')" :value="$metrics['reminder_rules']" icon="bell" />
        <x-admin.kpi-widget :label="__('Auto Escalate')" :value="$metrics['auto_escalate_rules']" icon="switch-horizontal" />
    </div>

    <x-admin.card>
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-sm text-slate-600">
                {{ __(':count escalation rules configured', ['count' => count($rows)]) }}
            </p>
            @if ($canManage)
                <a href="{{ route('admin.governance.escalations.create', $scopeQuery) }}" class="erp-btn erp-btn--primary">
                    {{ __('Create Escalation Rule') }}
                </a>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="erp-table erp-table--grid min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="py-3 pr-3">{{ __('Workflow') }}</th>
                        <th class="py-3 px-2">{{ __('Waiting Period') }}</th>
                        <th class="py-3 px-2">{{ __('Escalation Target') }}</th>
                        <th class="py-3 px-2">{{ __('Method') }}</th>
                        <th class="py-3 px-2">{{ __('Status') }}</th>
                        <th class="py-3 pl-2 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-erp-border">
                            <td class="py-3 pr-3">
                                <div class="font-medium">{{ $row['workflow'] }}</div>
                                <div class="text-xs text-slate-500">{{ $row['name'] }}</div>
                            </td>
                            <td class="py-3 px-2">{{ $row['waiting_period'] }}</td>
                            <td class="py-3 px-2">{{ $row['escalation_target_role'] }}</td>
                            <td class="py-3 px-2">
                                @if ($row['escalation_method_key'] === 'reminder')
                                    <span class="erp-badge erp-badge--info">{{ $row['escalation_method'] }}</span>
                                @else
                                    <span class="erp-badge erp-badge--warning">{{ $row['escalation_method'] }}</span>
                                @endif
                            </td>
                            <td class="py-3 px-2">
                                @if ($row['is_operational'])
                                    <span class="erp-badge erp-badge--success">{{ $row['status'] }}</span>
                                @else
                                    <span class="erp-badge erp-badge--muted">{{ $row['status'] }}</span>
                                @endif
                            </td>
                            <td class="py-3 pl-2 text-right">
                                @if ($canManage)
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.governance.escalations.edit', ['escalation' => $row['id']] + $scopeQuery) }}" class="erp-btn erp-btn--ghost erp-btn--sm">
                                            {{ __('Edit') }}
                                        </a>
                                        @if ($row['is_operational'])
                                            <form method="POST" action="{{ route('admin.governance.escalations.deactivate', ['escalation' => $row['id']] + $scopeQuery) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="erp-btn erp-btn--ghost erp-btn--sm text-red-600">
                                                    {{ __('Deactivate') }}
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.governance.escalations.activate', ['escalation' => $row['id']] + $scopeQuery) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="erp-btn erp-btn--ghost erp-btn--sm text-emerald-600">
                                                    {{ __('Activate') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                {{ __('No escalation rules configured for this scope.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</x-admin-layout>
