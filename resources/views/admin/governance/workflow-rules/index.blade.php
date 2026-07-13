@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $sectionUrl = route('admin.workspaces.administration.section', ['section' => 'workflow-governance']);
@endphp

<x-admin-layout
    :title="__('Workflow Rules')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance'), 'url' => $sectionUrl],
        ['label' => __('Workflow Rules')],
    ]"
>
    <x-admin.page-header
        :title="__('Workflow Rules')"
        :description="__('Automate ERP actions with IF condition THEN action rules. Triggers fire on document lifecycle events across commercial, production, and finance workflows.')"
    >
        @if ($canCreate)
            <x-slot:actions>
                <a href="{{ route('admin.governance.workflow-rules.create', $scopeQuery) }}" class="erp-btn-primary">
                    {{ __('New workflow rule') }}
                </a>
            </x-slot:actions>
        @endif
    </x-admin.page-header>

@include('admin.settings.partials.scope-selector', [
        'action' => route('admin.governance.workflow-rules.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
    ])

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <x-admin.kpi-widget :label="__('Total Rules')" :value="$metrics['total']" icon="cog" />
        <x-admin.kpi-widget :label="__('Active Rules')" :value="$metrics['active']" icon="badge-check" />
        <x-admin.kpi-widget :label="__('Draft Rules')" :value="$metrics['draft']" icon="document-text" />
        <x-admin.kpi-widget :label="__('Inactive Rules')" :value="$metrics['inactive']" icon="x-circle" />
    </div>

    <section class="erp-card mb-6">
        <div class="mb-4">
            <h2 class="text-base font-semibold text-erp-primary">{{ __('Rule Builder') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('IF condition THEN action — rules execute automatically when matching triggers fire.') }}</p>
        </div>

        @if ($rules->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No workflow rules configured for this scope yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="erp-table erp-table--grid">
                    <thead>
                        <tr>
                            <th>{{ __('Rule Name') }}</th>
                            <th>{{ __('Entity') }}</th>
                            <th>{{ __('Trigger') }}</th>
                            <th>{{ __('Conditions') }}</th>
                            <th>{{ __('Actions') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rules as $rule)
                            <tr>
                                <td class="font-medium text-slate-800">{{ $rule->name }}</td>
                                <td>{{ __(config('workflow_rule_registry.entities.'.$rule->entity_type.'.label', $rule->entity_type)) }}</td>
                                <td>{{ $rule->trigger->label() }}</td>
                                <td>{{ count($rule->conditions_json ?? []) }}</td>
                                <td>{{ $rule->actions->count() }}</td>
                                <td>
                                    <span class="erp-badge erp-badge--{{ $rule->status->value === 'active' ? 'success' : ($rule->status->value === 'draft' ? 'warning' : 'neutral') }}">
                                        {{ $rule->status->label() }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <div class="flex justify-end gap-2">
                                        @if ($canManage)
                                            <a href="{{ route('admin.governance.workflow-rules.edit', $rule) }}" class="erp-btn-secondary text-xs">{{ __('Edit') }}</a>
                                            @if ($rule->status->value === 'active')
                                                <form method="POST" action="{{ route('admin.governance.workflow-rules.deactivate', $rule) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="erp-btn-secondary text-xs">{{ __('Deactivate') }}</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.governance.workflow-rules.activate', $rule) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="erp-btn-primary text-xs">{{ __('Activate') }}</button>
                                                </form>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="erp-card">
        <h2 class="mb-4 text-base font-semibold text-erp-primary">{{ __('Recent Executions') }}</h2>
        @if ($recentExecutions->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No workflow executions recorded yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="erp-table erp-table--grid">
                    <thead>
                        <tr>
                            <th>{{ __('Rule') }}</th>
                            <th>{{ __('Trigger') }}</th>
                            <th>{{ __('Action') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Executed At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentExecutions as $execution)
                            <tr>
                                <td>{{ $execution->rule?->name }}</td>
                                <td>{{ $execution->trigger->label() }}</td>
                                <td>{{ $execution->action?->action_type?->label() ?? '—' }}</td>
                                <td>{{ $execution->status->label() }}</td>
                                <td>{{ $execution->executed_at?->toDateTimeString() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-admin-layout>
