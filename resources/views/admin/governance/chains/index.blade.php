@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $sectionUrl = route('admin.workspaces.administration.section', ['section' => 'workflow-governance']);
@endphp

<x-admin-layout
    :title="__('Approval Chains')"
    :breadcrumbs="[
        ['label' => __('Administration')],
        ['label' => __('Workflow & Governance'), 'url' => $sectionUrl],
        ['label' => __('Approval Chains')],
    ]"
>
    <x-admin.page-header
        :title="__('Approval Chains')"
        :description="__('Define who approves and in what order once approval rules trigger. Chains extend existing approval rules — they do not replace threshold configuration.')"
    >
        @if ($canCreate)
            <x-slot:actions>
                <a href="{{ route('admin.governance.chains.create', $scopeQuery) }}" class="erp-btn-primary">
                    {{ __('New approval chain') }}
                </a>
            </x-slot:actions>
        @endif
    </x-admin.page-header>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @include('admin.settings.partials.scope-selector', [
        'action' => route('admin.governance.chains.index'),
        'companyId' => $companyId,
        'branchId' => $branchId,
        'companies' => $companies,
        'branches' => $branches,
    ])

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        <x-admin.kpi-widget :label="__('Total Chains')" :value="$metrics['total']" icon="switch-horizontal" />
        <x-admin.kpi-widget :label="__('Active Chains')" :value="$metrics['active']" icon="badge-check" />
        <x-admin.kpi-widget :label="__('Pending Runs')" :value="$metrics['pending_runs']" icon="clock" />
        <x-admin.kpi-widget :label="__('Approved Runs')" :value="$metrics['approved_runs']" icon="check-circle" />
        <x-admin.kpi-widget :label="__('Rejected Runs')" :value="$metrics['rejected_runs']" icon="x-circle" />
    </div>

    <section class="erp-card mb-6">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold text-erp-primary">{{ __('Chain Configuration') }}</h2>
                <p class="mt-1 text-sm text-slate-500">{{ __('Linked to approval rules by rule type. Only one active chain per rule type at each scope.') }}</p>
            </div>
        </div>

        @if ($chains->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No approval chains configured for this scope yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="erp-table erp-table--grid">
                    <thead>
                        <tr>
                            <th>{{ __('Chain Name') }}</th>
                            <th>{{ __('Module') }}</th>
                            <th>{{ __('Document Type') }}</th>
                            <th>{{ __('Approval Mode') }}</th>
                            <th>{{ __('Linked Rule') }}</th>
                            <th>{{ __('Steps') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($chains as $chain)
                            <tr>
                                <td class="font-medium text-slate-800">{{ $chain->name }}</td>
                                <td>{{ __(config('chain_registry.modules.'.$chain->module.'.label', $chain->module)) }}</td>
                                <td>{{ $chain->document_type ? __(config('chain_registry.document_types.'.$chain->document_type.'.label', $chain->document_type)) : '—' }}</td>
                                <td>{{ $chain->approval_mode->label() }}</td>
                                <td>{{ __(config('approval_registry.rule_types.'.$chain->approval_rule_type->value.'.label', $chain->approval_rule_type->value)) }}</td>
                                <td>{{ $chain->steps->count() }}</td>
                                <td>
                                    <x-admin.status-badge :variant="$chain->status->badgeVariant()">
                                        {{ $chain->status->label() }}
                                    </x-admin.status-badge>
                                </td>
                                <td class="text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        @if ($canEdit)
                                            <a href="{{ route('admin.governance.chains.edit', ['chain' => $chain] + $scopeQuery) }}" class="text-sm font-medium text-erp-accent hover:underline">
                                                {{ __('Edit') }}
                                            </a>
                                        @endif
                                        @if ($canActivate && $chain->status !== \App\Enums\ApprovalChainStatus::Active)
                                            <form method="POST" action="{{ route('admin.governance.chains.activate', $chain) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-sm font-medium text-emerald-700 hover:underline">{{ __('Activate') }}</button>
                                            </form>
                                        @endif
                                        @if ($canActivate && $chain->status === \App\Enums\ApprovalChainStatus::Active)
                                            <form method="POST" action="{{ route('admin.governance.chains.deactivate', $chain) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-sm font-medium text-red-600 hover:underline">{{ __('Deactivate') }}</button>
                                            </form>
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
        <div class="mb-4">
            <h2 class="text-base font-semibold text-erp-primary">{{ __('Approval Monitor') }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ __('Recent approval runs and step outcomes.') }}</p>
        </div>

        @if ($recentRuns->isEmpty())
            <p class="text-sm text-slate-500">{{ __('No approval runs recorded yet.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="erp-table erp-table--grid">
                    <thead>
                        <tr>
                            <th>{{ __('Chain') }}</th>
                            <th>{{ __('Rule Type') }}</th>
                            <th>{{ __('Run Status') }}</th>
                            <th>{{ __('Step Outcomes') }}</th>
                            <th>{{ __('Started') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentRuns as $run)
                            <tr>
                                <td class="font-medium">{{ $run->chain?->name ?? '—' }}</td>
                                <td>{{ __(config('approval_registry.rule_types.'.$run->approval_rule_type->value.'.label', $run->approval_rule_type->value)) }}</td>
                                <td>{{ $run->status->label() }}</td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($run->stepRecords as $record)
                                            <x-admin.status-badge :variant="$record->status->badgeVariant()" class="!text-[10px]">
                                                #{{ $record->step_number }} {{ $record->status->label() }}
                                            </x-admin.status-badge>
                                        @endforeach
                                    </div>
                                </td>
                                <td>{{ $run->started_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-admin-layout>
