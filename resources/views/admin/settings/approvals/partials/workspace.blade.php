@props(['row', 'canManage', 'companyId', 'branchId', 'roles', 'permissions'])

@php
    $scopeQuery = array_filter([
        'company_id' => $companyId,
        'branch_id' => $branchId,
    ]);
    $approvalsLandingUrl = route('admin.settings.approvals.index', $scopeQuery);
@endphp

@include('admin.settings.partials.hub-toolbar', [
    'title' => $row['label'],
    'description' => $row['description'],
    'backUrl' => $approvalsLandingUrl,
    'backLabel' => __('All approval rules'),
])

@if ($canManage)
    <form method="POST" action="{{ route('admin.settings.approvals.update') }}" class="space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <input type="hidden" name="return_rule" value="{{ $row['rule_type'] }}">
        @if ($branchId)
            <input type="hidden" name="branch_id" value="{{ $branchId }}">
        @endif
@endif

@include('admin.settings.approvals.partials.workspace-panel', [
    'row' => $row,
    'canManage' => $canManage,
    'roles' => $roles,
    'permissions' => $permissions,
])

@if ($canManage)
        <div class="sticky bottom-4 z-10 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-erp-border bg-erp-card px-5 py-4 shadow-lg">
            <p class="text-xs text-slate-500">
                {{ __('Save applies to this approval rule for the selected company/branch scope.') }}
            </p>
            <x-primary-button>{{ __('Save approval rule') }}</x-primary-button>
        </div>
    </form>
@endif
